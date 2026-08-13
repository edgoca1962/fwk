<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Manifest;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Describe la identidad, dependencias y recursos de un módulo.
 *
 * ModuleManifest no registra directamente CPT, páginas,
 * taxonomías ni servicios. Solamente describe los recursos
 * que pertenecen al módulo.
 *
 * Los registradores especializados consumirán posteriormente
 * esta información.
 *
 * @package FWK
 */
final class ModuleManifest
{
   /**
    * Directorio raíz del módulo.
    */
   private string $moduleDirectory;

   /**
    * Configuración original del manifiesto.
    *
    * @var array<string, mixed>
    */
   private array $manifest;

   /**
    * Recursos ya cargados.
    *
    * @var array<string, array<string, mixed>>
    */
   private array $loadedResources = [];

   /**
    * @param array<string, mixed> $manifest
    */
   public function __construct(
      string $moduleDirectory,
      array $manifest
   ) {
      $this->moduleDirectory = untrailingslashit(
         wp_normalize_path($moduleDirectory)
      );

      $this->manifest = $this->normalize_manifest(
         $manifest
      );

      $this->validate();
   }

   /**
    * Devuelve el identificador único del módulo.
    */
   public function get_slug(): string
   {
      return (string) $this->manifest['slug'];
   }

   /**
    * Devuelve el nombre descriptivo.
    */
   public function get_name(): string
   {
      return __((string) $this->manifest['name'], 'FWK');
   }

   /**
    * Devuelve la descripción.
    */
   public function get_description(): string
   {
      return __((string) $this->manifest['description'], 'FWK');
   }

   /**
    * Devuelve la versión del módulo.
    */
   public function get_version(): string
   {
      return (string) $this->manifest['version'];
   }

   /**
    * Devuelve el directorio raíz del módulo.
    */
   public function get_directory(): string
   {
      return $this->moduleDirectory;
   }

   /**
    * Devuelve las dependencias obligatorias.
    *
    * Los valores deben ser slugs de otros módulos.
    *
    * @return string[]
    */
   public function get_dependencies(): array
   {
      return $this->manifest['dependencies'];
   }

   /**
    * Devuelve las dependencias opcionales.
    *
    * @return string[]
    */
   public function get_optional_dependencies(): array
   {
      return $this->manifest['optional_dependencies'];
   }

   /**
    * Devuelve los módulos incompatibles.
    *
    * @return string[]
    */
   public function get_conflicts(): array
   {
      return $this->manifest['conflicts'];
   }

   /**
    * Indica si el manifiesto declara un recurso.
    */
   public function has_resource(string $resource): bool
   {
      $resource = sanitize_key($resource);

      if ($resource === '') {
         return false;
      }

      return array_key_exists(
         $resource,
         $this->manifest['resources']
      );
   }

   /**
    * Devuelve un recurso del módulo.
    *
    * El recurso puede estar declarado directamente como arreglo
    * o mediante una ruta hacia un archivo PHP que devuelve un arreglo.
    *
    * @return array<string, mixed>
    */
   public function get_resource(
      string $resource
   ): array {
      $resource = sanitize_key($resource);

      if ($resource === '') {
         return [];
      }

      if (isset($this->loadedResources[$resource])) {
         return $this->loadedResources[$resource];
      }

      $definition = $this->manifest['resources'][$resource]
         ?? [];

      $values = $this->resolve_resource(
         $resource,
         $definition
      );

      $this->loadedResources[$resource] = $values;

      return $values;
   }

   /**
    * Devuelve las páginas declaradas.
    *
    * @return array<string, mixed>
    */
   public function get_pages(): array
   {
      return $this->get_resource('pages');
   }

   /**
    * Devuelve las definiciones normalizadas de Post Types.
    *
    * Cada CPT puede declararse directamente como arreglo
    * o mediante una ruta relativa al módulo.
    *
    * @return array<string, array<string, mixed>>
    */
   public function get_post_types(): array
   {
      $postTypes = $this->get_resource(
         'post_types'
      );

      $resolved = [];

      foreach ($postTypes as $slug => $definition) {
         if (!is_string($slug)) {
            continue;
         }

         if (is_array($definition)) {
            $resolved[$slug] = $definition;
            continue;
         }

         if (!is_string($definition)) {
            continue;
         }

         $file = $this->resolve_file_path(
            $definition
         );

         if (!is_readable($file)) {
            throw new \RuntimeException(
               sprintf(
                  'El Post Type "%s" del módulo "%s" apunta a un archivo inexistente: %s',
                  $slug,
                  $this->get_slug(),
                  $file
               )
            );
         }

         $config = require $file;

         if (!is_array($config)) {
            throw new \UnexpectedValueException(
               sprintf(
                  'La definición del Post Type "%s" debe devolver un arreglo.',
                  $slug
               )
            );
         }

         $resolved[$slug] = $config;
      }

      return $resolved;
   }

   /**
    * Devuelve las taxonomías declaradas.
    *
    * @return array<string, mixed>
    */
   public function get_taxonomies(): array
   {
      return $this->get_resource('taxonomies');
   }

   /**
    * Devuelve la configuración visual.
    *
    * @return array<string, mixed>
    */
   public function get_views(): array
   {
      return $this->get_resource('views');
   }

   /**
    * Devuelve las definiciones de metadata del módulo.
    *
    * @return array<string, mixed>
    */
   public function get_meta(): array
   {
      return $this->get_resource('meta');
   }
   /**
    * Devuelve todos los tipos de recursos declarados.
    *
    * @return string[]
    */
   public function get_resource_names(): array
   {
      return array_keys(
         $this->manifest['resources']
      );
   }

   /**
    * Devuelve la definición normalizada del manifiesto.
    *
    * No obliga a cargar los archivos externos de recursos.
    *
    * @return array<string, mixed>
    */
   public function to_array(): array
   {
      return $this->manifest;
   }

   /**
    * Carga un recurso declarado.
    *
    * @return array<string, mixed>
    */
   private function resolve_resource(
      string $resource,
      mixed $definition
   ): array {
      if (is_array($definition)) {
         return $definition;
      }

      if (!is_string($definition)) {
         return [];
      }

      $file = $this->resolve_file_path(
         $definition
      );

      if (!is_readable($file)) {
         _doing_it_wrong(
            __METHOD__,
            sprintf(
               'El recurso "%s" del módulo "%s" apunta a un archivo que no existe: %s',
               esc_html($resource),
               esc_html($this->get_slug()),
               esc_html($file)
            ),
            $this->get_version()
         );

         return [];
      }

      $values = require $file;

      if (!is_array($values)) {
         throw new \UnexpectedValueException(
            sprintf(
               'El archivo de recurso "%s" debe devolver un arreglo.',
               $file
            )
         );
      }

      return $values;
   }

   /**
    * Resuelve rutas absolutas o relativas al módulo.
    */
   private function resolve_file_path(
      string $file
   ): string {
      $file = wp_normalize_path($file);

      /*
       * Ruta absoluta Unix o Windows.
       */
      if (
         str_starts_with($file, '/')
         || preg_match('/^[A-Za-z]:\//', $file) === 1
      ) {
         return $file;
      }

      return $this->moduleDirectory
         . '/'
         . ltrim($file, '/');
   }

   /**
    * Aplica valores predeterminados.
    *
    * @param array<string, mixed> $manifest
    *
    * @return array<string, mixed>
    */
   private function normalize_manifest(
      array $manifest
   ): array {
      $defaults = [
         'slug' => '',
         'name' => '',
         'description' => '',
         'version' => '1.0.0',

         'dependencies' => [],
         'optional_dependencies' => [],
         'conflicts' => [],

         'resources' => [
            'pages' => [],
            'post_types' => [],
            'taxonomies' => [],
            'meta' => [],
            'views' => [],
            'roles' => [],
            'services' => [],
            'assets' => [],
            'ajax' => [],
            'rest' => [],
         ],
      ];

      $manifest = array_replace_recursive(
         $defaults,
         $manifest
      );

      $manifest['slug'] = sanitize_key(
         (string) $manifest['slug']
      );

      $manifest['name'] = sanitize_text_field(
         (string) $manifest['name']
      );

      $manifest['description'] = sanitize_textarea_field(
         (string) $manifest['description']
      );

      $manifest['version'] = sanitize_text_field(
         (string) $manifest['version']
      );

      $manifest['dependencies'] = $this->normalize_slugs(
         $manifest['dependencies']
      );

      $manifest['optional_dependencies'] = $this->normalize_slugs(
         $manifest['optional_dependencies']
      );

      $manifest['conflicts'] = $this->normalize_slugs(
         $manifest['conflicts']
      );

      return $manifest;
   }

   /**
    * Valida los elementos obligatorios.
    */
   private function validate(): void
   {
      if ($this->get_slug() === '') {
         throw new \InvalidArgumentException(
            'El manifiesto debe declarar un slug válido.'
         );
      }

      if (trim((string) $this->manifest['name']) === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'El módulo "%s" debe declarar un nombre.',
               $this->get_slug()
            )
         );
      }

      if (
         !is_array($this->manifest['resources'])
      ) {
         throw new \InvalidArgumentException(
            sprintf(
               'Los recursos del módulo "%s" deben ser un arreglo.',
               $this->get_slug()
            )
         );
      }
   }

   /**
    * Normaliza una lista de slugs.
    *
    * @return string[]
    */
   private function normalize_slugs(
      mixed $values
   ): array {
      if (!is_array($values)) {
         return [];
      }

      $slugs = array_map(
         static fn(mixed $value): string => sanitize_key(
            (string) $value
         ),
         $values
      );

      return array_values(
         array_unique(
            array_filter($slugs)
         )
      );
   }
}