<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Registry;

use FWK\Modules\Core\Content\MetaDefinition;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Support\Singleton;
use FWK\Modules\Core\Content\PostTypeDefinition;


if (!defined('ABSPATH')) {
   exit;
}

/**
 * Registro central de Post Meta de WP FRW.
 *
 * Responsabilidades:
 *
 * - Recibir metadata declarada por los módulos.
 * - Convertir cada definición en MetaDefinition.
 * - Validar que el Post Type exista.
 * - Detectar claves duplicadas por Post Type.
 * - Registrar metadata mediante register_post_meta().
 * - Mantener información para diagnóstico.
 *
 * @package FWK
 */
final class MetaRegistry
{
   use Singleton;

   /**
    * Definiciones registradas.
    *
    * Estructura:
    *
    * [
    *    'billetera' => [
    *       '_moneda' => MetaDefinition
    *    ]
    * ]
    *
    * @var array<string, array<string, MetaDefinition>>
    */
   private array $definitions = [];

   /**
    * Módulo propietario de cada definición.
    *
    * @var array<string, array<string, string>>
    */
   private array $owners = [];

   /**
    * Metadata registrada correctamente.
    *
    * @var array<string, string[]>
    */
   private array $registered = [];

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
    * Indica si el callback de init fue registrado.
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
    * Incorpora la metadata declarada por un módulo.
    */
   public function register_module(
      ModuleInterface $module
   ): self {
      $postTypes = $module
         ->manifest()
         ->get_post_types();

      foreach ($postTypes as $postType => $config) {
         if (
            !is_string($postType)
            || !is_array($config)
         ) {
            continue;
         }

         try {
            $postTypeDefinition =
               new PostTypeDefinition(
                  $postType,
                  $config
               );
            if (!$postTypeDefinition->has_resource('meta')) {
               continue;
            }

            $resource = $postTypeDefinition
               ->get_resource('meta');

            $definitions = $this->load_meta_resource(
               $module,
               $postType,
               $resource
            );
         } catch (\InvalidArgumentException $exception) {
            $this->errors[] = sprintf(
               'No fue posible procesar "%s": %s',
               $postType,
               $exception->getMessage()
            );

            continue;
         }

         if (
            !$postTypeDefinition
               ->has_resource('meta')
         ) {
            continue;
         }

         $resource =
            $postTypeDefinition
               ->get_resource('meta');

         $definitions =
            $this->load_meta_resource(
               $module,
               $postType,
               $resource
            );

         foreach ($definitions as $key => $definition) {
            if (
               !is_string($key)
               || !is_array($definition)
            ) {
               continue;
            }

            try {
               $metaDefinition =
                  new MetaDefinition(
                     $postType,
                     $key,
                     $definition
                  );
            } catch (\InvalidArgumentException $exception) {
               $this->errors[] =
                  $exception->getMessage();

               continue;
            }

            $this->register(
               $module,
               $metaDefinition
            );
         }
      }

      return $this;
   }
   /**
    * Registra una definición individual.
    */
   public function register(
      ModuleInterface $module,
      MetaDefinition $definition
   ): self {
      $postType = $definition->get_post_type();
      $key = $definition->get_key();
      $moduleSlug = $module->get_slug();

      if (
         isset(
         $this->definitions[$postType][$key]
      )
      ) {
         $existingOwner =
            $this->owners[$postType][$key]
            ?? 'desconocido';

         if ($existingOwner !== $moduleSlug) {
            $this->errors[] = sprintf(
               'El meta "%s" del Post Type "%s" fue declarado por los módulos "%s" y "%s".',
               $key,
               $postType,
               $existingOwner,
               $moduleSlug
            );
         }

         return $this;
      }

      $this->definitions[$postType][$key] =
         $definition;

      $this->owners[$postType][$key] =
         $moduleSlug;

      do_action(
         'fwk_meta_definition_registered',
         $definition,
         $module,
         $this
      );

      return $this;
   }

   /**
    * Registra el callback de WordPress.
    */
   public function boot(): void
   {
      if ($this->hookRegistered) {
         return;
      }

      $this->hookRegistered = true;

      /*
       * PostTypeRegistry registra CPT en prioridad 5.
       * TaxonomyRegistry registra taxonomías en 10.
       *
       * Metadata se registra después.
       */
      add_action(
         'init',
         [$this, 'register_meta'],
         15
      );
   }
   /**
    * Carga el recurso meta declarado por un CPT.
    *
    * @return array<string, mixed>
    */
   private function load_meta_resource(
      ModuleInterface $module,
      string $postType,
      mixed $resource
   ): array {
      /*
       * Permite declarar metadata inline.
       */
      if (is_array($resource)) {
         return $resource;
      }

      if (!is_string($resource)) {
         $this->errors[] = sprintf(
            'El recurso meta del Post Type "%s" del módulo "%s" debe ser un arreglo o una ruta.',
            $postType,
            $module->get_slug()
         );

         return [];
      }

      $file = $module
         ->manifest()
         ->get_directory()
         . '/'
         . ltrim(
            wp_normalize_path($resource),
            '/'
         );

      if (!is_readable($file)) {
         $this->errors[] = sprintf(
            'El archivo de metadata del Post Type "%s" no existe o no es legible: %s',
            $postType,
            $file
         );

         return [];
      }

      $definitions = require $file;

      if (!is_array($definitions)) {
         $this->errors[] = sprintf(
            'El archivo de metadata "%s" debe devolver un arreglo.',
            $file
         );

         return [];
      }

      return $definitions;
   }
   /**
    * Materializa las definiciones mediante register_post_meta().
    */
   public function register_meta(): void
   {
      if ($this->booted) {
         return;
      }

      $postTypes = PostTypeRegistry::get_instance();

      foreach (
         $this->definitions
         as $postType => $definitions
      ) {
         /*
          * Validamos primero el Post Type.
          */
         if (
            !$this->validate_post_type(
               $postType,
               $postTypes
            )
         ) {
            continue;
         }

         foreach ($definitions as $key => $definition) {
            if (!$definition->is_enabled()) {
               continue;
            }

            $result = register_post_meta(
               $postType,
               $key,
               $definition->get_args()
            );

            if (!$result) {
               $this->errors[] = sprintf(
                  'No fue posible registrar el meta "%s" para el Post Type "%s".',
                  $key,
                  $postType
               );

               continue;
            }

            $this->registered[$postType][] =
               $key;

            do_action(
               'fwk_post_meta_registered',
               $postType,
               $key,
               $definition,
               $this
            );
         }

         if (
            isset($this->registered[$postType])
         ) {
            $this->registered[$postType] =
               array_values(
                  array_unique(
                     $this->registered[$postType]
                  )
               );
         }
      }

      $this->errors = array_values(
         array_unique($this->errors)
      );

      $this->warnings = array_values(
         array_unique($this->warnings)
      );

      $this->booted = true;
   }

   /**
    * Valida que el Post Type exista y esté disponible.
    */
   private function validate_post_type(
      string $postType,
      PostTypeRegistry $postTypes
   ): bool {
      /*
       * Si FRW conoce el Post Type, verificamos
       * que no esté deshabilitado.
       */
      if ($postTypes->has($postType)) {
         $definition = $postTypes->get(
            $postType
         );

         if (
            $definition !== null
            && !$definition->is_enabled()
         ) {
            $this->warnings[] = sprintf(
               'La metadata del Post Type "%s" no fue registrada porque ese Post Type está deshabilitado.',
               $postType
            );

            return false;
         }
      }

      /*
       * En init prioridad 15, los CPT ya deberían
       * existir si fueron registrados por FRW.
       *
       * También admitimos CPT nativos o externos.
       */
      if (!post_type_exists($postType)) {
         $this->errors[] = sprintf(
            'No se puede registrar metadata para el Post Type "%s" porque no existe.',
            $postType
         );

         return false;
      }

      return true;
   }

   /**
    * Indica si existe una definición.
    */
   public function has(
      string $postType,
      string $key
   ): bool {
      $postType = sanitize_key($postType);
      $key = sanitize_key($key);

      return isset(
         $this->definitions[$postType][$key]
      );
   }

   /**
    * Devuelve una definición.
    */
   public function get(
      string $postType,
      string $key
   ): ?MetaDefinition {
      $postType = sanitize_key($postType);
      $key = sanitize_key($key);

      return $this->definitions[$postType][$key]
         ?? null;
   }

   /**
    * Devuelve todas las definiciones.
    *
    * @return array<string, array<string, MetaDefinition>>
    */
   public function all(): array
   {
      return $this->definitions;
   }

   /**
    * Devuelve las claves registradas de un Post Type.
    *
    * @return string[]
    */
   public function get_registered(
      string $postType
   ): array {
      return $this->registered[
         sanitize_key($postType)
      ] ?? [];
   }

   /**
    * Devuelve todos los Post Types que tienen
    * definiciones de metadata.
    *
    * @return string[]
    */
   public function get_post_types(): array
   {
      return array_keys(
         $this->definitions
      );
   }

   /**
    * Devuelve el módulo propietario.
    */
   public function get_owner(
      string $postType,
      string $key
   ): ?string {
      $postType = sanitize_key($postType);
      $key = sanitize_key($key);

      return $this->owners[$postType][$key]
         ?? null;
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