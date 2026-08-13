<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Registry;

use FWK\Modules\Core\Content\TaxonomyDefinition;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Registro central de taxonomías de WP FRW.
 *
 * Responsabilidades:
 *
 * - Recibir taxonomías declaradas por los módulos.
 * - Crear TaxonomyDefinition.
 * - Detectar slugs duplicados.
 * - Validar object types.
 * - Ignorar taxonomías nativas.
 * - Registrar taxonomías personalizadas en WordPress.
 * - Mantener información para diagnóstico.
 *
 * @package FWK
 */
final class TaxonomyRegistry
{
   use Singleton;

   /**
    * Definiciones de taxonomías.
    *
    * @var array<string, TaxonomyDefinition>
    */
   private array $definitions = [];

   /**
    * Módulo propietario de cada taxonomía.
    *
    * @var array<string, string>
    */
   private array $owners = [];

   /**
    * Taxonomías registradas por FRW.
    *
    * @var string[]
    */
   private array $registered = [];

   /**
    * Object types efectivamente asociados.
    *
    * @var array<string, string[]>
    */
   private array $objectTypes = [];

   /**
    * Errores.
    *
    * @var string[]
    */
   private array $errors = [];

   /**
    * Advertencias.
    *
    * @var string[]
    */
   private array $warnings = [];

   /**
    * Indica si el callback de init ya fue agregado.
    */
   private bool $hookRegistered = false;

   /**
    * Indica si ya se ejecutó el registro.
    */
   private bool $booted = false;

   protected function __construct()
   {
   }

   /**
    * Incorpora las taxonomías declaradas por un módulo.
    */
   public function register_module(
      ModuleInterface $module
   ): self {
      $taxonomies = $module
         ->manifest()
         ->get_taxonomies();

      foreach ($taxonomies as $slug => $definition) {
         if (!is_string($slug)) {
            $this->errors[] = sprintf(
               'El módulo "%s" contiene una taxonomía con slug inválido.',
               $module->get_slug()
            );

            continue;
         }

         if (!is_array($definition)) {
            $this->errors[] = sprintf(
               'La definición de la taxonomía "%s" del módulo "%s" debe ser un arreglo.',
               $slug,
               $module->get_slug()
            );

            continue;
         }

         try {
            $taxonomy = new TaxonomyDefinition(
               $slug,
               $definition
            );
         } catch (\InvalidArgumentException $exception) {
            $this->errors[] = sprintf(
               'Taxonomía "%s" del módulo "%s": %s',
               $slug,
               $module->get_slug(),
               $exception->getMessage()
            );

            continue;
         }

         $this->register(
            $module,
            $taxonomy
         );
      }

      return $this;
   }

   /**
    * Registra una definición individual.
    */
   public function register(
      ModuleInterface $module,
      TaxonomyDefinition $definition
   ): self {
      $slug = $definition->get_slug();
      $moduleSlug = $module->get_slug();

      /*
       * Por ahora una definición de taxonomía tiene un único
       * módulo propietario.
       *
       * Eso no impide que se asocie a múltiples CPT.
       */
      if (isset($this->definitions[$slug])) {
         $existingOwner = $this->owners[$slug]
            ?? 'desconocido';

         if ($existingOwner !== $moduleSlug) {
            $this->errors[] = sprintf(
               'La taxonomía "%s" fue declarada por los módulos "%s" y "%s".',
               $slug,
               $existingOwner,
               $moduleSlug
            );
         }

         return $this;
      }

      $this->definitions[$slug] = $definition;
      $this->owners[$slug] = $moduleSlug;

      do_action(
         'fwk_taxonomy_definition_registered',
         $definition,
         $module,
         $this
      );

      return $this;
   }

   /**
    * Registra el callback en init.
    */
   public function boot(): void
   {
      if ($this->hookRegistered) {
         return;
      }

      $this->hookRegistered = true;

      /*
       * PostTypeRegistry utiliza prioridad 5.
       * Registramos taxonomías después, para disponer de
       * los CPT personalizados cuando validemos relaciones.
       */
      add_action(
         'init',
         [$this, 'register_taxonomies'],
         10
      );
   }

   /**
    * Materializa las taxonomías.
    */
   public function register_taxonomies(): void
   {
      if ($this->booted) {
         return;
      }

      $postTypes = PostTypeRegistry::get_instance();

      foreach ($this->definitions as $slug => $definition) {
         if (!$definition->is_enabled()) {
            continue;
         }

         /*
          * Una taxonomía nativa ya existe en WordPress.
          *
          * Más adelante podremos asociarla a CPT adicionales
          * mediante register_taxonomy_for_object_type().
          */
         if ($definition->is_native()) {
            $this->register_native_relationships(
               $definition,
               $postTypes
            );

            continue;
         }

         if (taxonomy_exists($slug)) {
            $this->errors[] = sprintf(
               'La taxonomía "%s" ya existe en WordPress y no puede ser registrada nuevamente por FRW.',
               $slug
            );

            continue;
         }

         $validObjectTypes = $this->resolve_object_types(
            $definition,
            $postTypes
         );

         if ($validObjectTypes === []) {
            $this->errors[] = sprintf(
               'La taxonomía "%s" no tiene object types válidos para registrar.',
               $slug
            );

            continue;
         }

         $result = register_taxonomy(
            $slug,
            $validObjectTypes,
            $definition->get_args()
         );

         if (is_wp_error($result)) {
            $this->errors[] = sprintf(
               'No fue posible registrar la taxonomía "%s": %s',
               $slug,
               $result->get_error_message()
            );

            continue;
         }

         $this->registered[] = $slug;
         $this->objectTypes[$slug] = $validObjectTypes;

         do_action(
            'fwk_taxonomy_registered',
            $slug,
            $definition,
            $validObjectTypes,
            $this
         );
      }

      $this->registered = array_values(
         array_unique($this->registered)
      );

      $this->errors = array_values(
         array_unique($this->errors)
      );

      $this->warnings = array_values(
         array_unique($this->warnings)
      );

      $this->booted = true;
   }

   /**
    * Determina qué object types están realmente disponibles.
    *
    * @return string[]
    */
   private function resolve_object_types(
      TaxonomyDefinition $definition,
      PostTypeRegistry $postTypes
   ): array {
      $valid = [];

      foreach ($definition->get_object_types() as $objectType) {
         /*
          * Si FRW conoce el Post Type, comprobamos su definición.
          */
         if ($postTypes->has($objectType)) {
            $postTypeDefinition = $postTypes->get(
               $objectType
            );

            if (
               $postTypeDefinition !== null
               && !$postTypeDefinition->is_enabled()
            ) {
               $this->warnings[] = sprintf(
                  'La taxonomía "%s" no se asoció con "%s" porque ese Post Type está deshabilitado.',
                  $definition->get_slug(),
                  $objectType
               );

               continue;
            }
         }

         /*
          * En init prioridad 10, los CPT de FRW ya deberían
          * haber sido registrados en prioridad 5.
          *
          * También admitimos Post Types nativos o externos.
          */
         if (!post_type_exists($objectType)) {
            $this->warnings[] = sprintf(
               'La taxonomía "%s" solicita el object type "%s", pero este no existe.',
               $definition->get_slug(),
               $objectType
            );

            continue;
         }

         $valid[] = $objectType;
      }

      return array_values(
         array_unique($valid)
      );
   }

   /**
    * Asocia una taxonomía nativa a object types adicionales.
    */
   private function register_native_relationships(
      TaxonomyDefinition $definition,
      PostTypeRegistry $postTypes
   ): void {
      $slug = $definition->get_slug();

      if (!taxonomy_exists($slug)) {
         $this->errors[] = sprintf(
            'La taxonomía "%s" fue declarada como nativa, pero WordPress no la tiene registrada.',
            $slug
         );

         return;
      }

      $validObjectTypes = $this->resolve_object_types(
         $definition,
         $postTypes
      );

      foreach ($validObjectTypes as $objectType) {
         if (
            register_taxonomy_for_object_type(
               $slug,
               $objectType
            )
         ) {
            $this->objectTypes[$slug][] = $objectType;
         }
      }

      $this->objectTypes[$slug] = array_values(
         array_unique(
            $this->objectTypes[$slug] ?? []
         )
      );
   }

   /**
    * Indica si existe una definición.
    */
   public function has(string $slug): bool
   {
      return isset(
         $this->definitions[
            sanitize_key($slug)
         ]
      );
   }

   /**
    * Devuelve una definición.
    */
   public function get(
      string $slug
   ): ?TaxonomyDefinition {
      $slug = sanitize_key($slug);

      return $this->definitions[$slug]
         ?? null;
   }

   /**
    * Devuelve el módulo propietario.
    */
   public function get_owner(
      string $slug
   ): ?string {
      return $this->owners[
         sanitize_key($slug)
      ] ?? null;
   }

   /**
    * Devuelve todas las definiciones.
    *
    * @return array<string, TaxonomyDefinition>
    */
   public function all(): array
   {
      return $this->definitions;
   }

   /**
    * Devuelve las taxonomías registradas por FRW.
    *
    * @return string[]
    */
   public function get_registered(): array
   {
      return $this->registered;
   }

   /**
    * Devuelve los object types asociados.
    *
    * @return string[]
    */
   public function get_object_types(
      string $slug
   ): array {
      return $this->objectTypes[
         sanitize_key($slug)
      ] ?? [];
   }

   /**
    * Devuelve errores.
    *
    * @return string[]
    */
   public function get_errors(): array
   {
      return $this->errors;
   }

   /**
    * Devuelve advertencias.
    *
    * @return string[]
    */
   public function get_warnings(): array
   {
      return $this->warnings;
   }

   /**
    * Indica si el Registry ya fue ejecutado.
    */
   public function is_booted(): bool
   {
      return $this->booted;
   }
}