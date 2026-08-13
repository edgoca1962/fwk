<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Registry;

use FWK\Modules\Core\Content\PostTypeDefinition;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Registro central de Post Types de WP FRW.
 *
 * Responsabilidades:
 *
 * - Recibir definiciones declaradas por los módulos.
 * - Convertirlas en PostTypeDefinition.
 * - Detectar slugs duplicados.
 * - Ignorar Post Types nativos.
 * - Registrar los CPT personalizados en WordPress.
 * - Mantener información de diagnóstico.
 *
 * @package FWK
 */
final class PostTypeRegistry
{
   use Singleton;

   /**
    * Definiciones registradas.
    *
    * @var array<string, PostTypeDefinition>
    */
   private array $definitions = [];

   /**
    * Módulo propietario de cada Post Type.
    *
    * @var array<string, string>
    */
   private array $owners = [];

   /**
    * Post Types registrados correctamente en WordPress.
    *
    * @var string[]
    */
   private array $registered = [];

   /**
    * Errores encontrados.
    *
    * @var string[]
    */
   private array $errors = [];

   /**
    * Indica si el hook init ya fue registrado.
    */
   private bool $hookRegistered = false;

   /**
    * Indica si ya se ejecutó el registro en WordPress.
    */
   private bool $booted = false;

   protected function __construct()
   {
   }

   /**
    * Registra todos los Post Types declarados por un módulo.
    */
   public function register_module(
      ModuleInterface $module
   ): self {
      $postTypes = $module
         ->manifest()
         ->get_post_types();

      foreach ($postTypes as $slug => $definition) {
         if (!is_string($slug)) {
            $this->errors[] = sprintf(
               'El módulo "%s" contiene un Post Type con slug inválido.',
               $module->get_slug()
            );

            continue;
         }

         if (!is_array($definition)) {
            $this->errors[] = sprintf(
               'La definición del Post Type "%s" del módulo "%s" debe ser un arreglo.',
               $slug,
               $module->get_slug()
            );

            continue;
         }

         $this->register(
            $module,
            new PostTypeDefinition(
               $slug,
               $definition
            )
         );
      }

      return $this;
   }

   /**
    * Registra una definición individual.
    */
   public function register(
      ModuleInterface $module,
      PostTypeDefinition $definition
   ): self {
      $slug = $definition->get_slug();
      $moduleSlug = $module->get_slug();

      /*
       * Un Post Type solamente puede pertenecer a un módulo.
       */
      if (isset($this->definitions[$slug])) {
         $existingOwner = $this->owners[$slug] ?? 'desconocido';

         if ($existingOwner !== $moduleSlug) {
            $this->errors[] = sprintf(
               'El Post Type "%s" fue declarado por los módulos "%s" y "%s".',
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
         'fwk_post_type_definition_registered',
         $definition,
         $module,
         $this
      );

      return $this;
   }

   /**
    * Conecta el Registry con el hook init de WordPress.
    */
   public function boot(): void
   {
      if ($this->hookRegistered) {
         return;
      }

      $this->hookRegistered = true;

      add_action(
         'init',
         [$this, 'register_post_types'],
         5
      );
   }

   /**
    * Materializa las definiciones mediante register_post_type().
    */
   public function register_post_types(): void
   {
      if ($this->booted) {
         return;
      }

      foreach ($this->definitions as $slug => $definition) {

         /*
          * Los CPT no habilitados no se vuelven
          * a registrar.
          */
         if (!$definition->is_enabled()) {
            continue;
         }

         /*
          * Los tipos nativos como "post" no se vuelven
          * a registrar.
          */
         if ($definition->is_native()) {
            continue;
         }

         /*
          * Evita sobrescribir accidentalmente un Post Type
          * que ya haya sido registrado externamente.
          */
         if (post_type_exists($slug)) {
            $this->errors[] = sprintf(
               'El Post Type "%s" ya existe en WordPress y no puede ser registrado nuevamente por FRW.',
               $slug
            );

            continue;
         }

         $result = register_post_type(
            $slug,
            $definition->get_args()
         );

         if (is_wp_error($result)) {
            $this->errors[] = sprintf(
               'No fue posible registrar el Post Type "%s": %s',
               $slug,
               $result->get_error_message()
            );

            continue;
         }

         $this->registered[] = $slug;

         do_action(
            'fwk_post_type_registered',
            $slug,
            $definition,
            $this
         );
      }

      $this->registered = array_values(
         array_unique($this->registered)
      );

      $this->errors = array_values(
         array_unique($this->errors)
      );

      $this->booted = true;
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
   ): ?PostTypeDefinition {
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
      $slug = sanitize_key($slug);

      return $this->owners[$slug]
         ?? null;
   }

   /**
    * Devuelve todas las definiciones.
    *
    * @return array<string, PostTypeDefinition>
    */
   public function all(): array
   {
      return $this->definitions;
   }

   /**
    * Devuelve los Post Types materializados.
    *
    * @return string[]
    */
   public function get_registered(): array
   {
      return $this->registered;
   }

   /**
    * Devuelve los errores encontrados.
    *
    * @return string[]
    */
   public function get_errors(): array
   {
      return $this->errors;
   }

   /**
    * Indica si ya se ejecutó el registro.
    */
   public function is_booted(): bool
   {
      return $this->booted;
   }
}