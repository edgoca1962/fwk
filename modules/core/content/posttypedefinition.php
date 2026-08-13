<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Content;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Representa y normaliza la definición de un Post Type.
 *
 * Esta clase:
 *
 * - valida el slug;
 * - aplica valores predeterminados;
 * - genera labels básicos;
 * - conserva argumentos adicionales de WordPress;
 * - distingue CPT nativos de personalizados;
 * - prepara el arreglo que posteriormente consumirá
 *   PostTypeRegistry.
 *
 * No registra directamente el CPT.
 *
 * @package FWK
 */
final class PostTypeDefinition
{
   /**
    * Identificador del post type.
    */
   private string $slug;

   /**
    * Definición normalizada.
    *
    * @var array<string, mixed>
    */
   private array $definition;

   /**
    * @param array<string, mixed> $definition
    */
   public function __construct(
      string $slug,
      array $definition = []
   ) {
      $this->slug = $this->normalize_slug($slug);

      $this->definition = $this->normalize(
         $definition
      );

      $this->validate();
   }

   /**
    * Devuelve el slug del post type.
    */
   public function get_slug(): string
   {
      return $this->slug;
   }

   /**
    * Indica si es un Post Type nativo de WordPress.
    */
   public function is_native(): bool
   {
      return (bool) $this->definition['native'];
   }

   /**
    * Devuelve el nombre singular.
    */
   public function get_singular(): string
   {
      return (string) $this->definition['singular'];
   }

   /**
    * Devuelve el nombre plural.
    */
   public function get_plural(): string
   {
      return (string) $this->definition['plural'];
   }

   /**
    * Devuelve las taxonomías relacionadas.
    *
    * @return string[]
    */
   public function get_taxonomies(): array
   {
      return $this->definition['taxonomies'];
   }

   /**
    * Devuelve los soportes declarados.
    *
    * @return array<int|string, mixed>
    */
   public function get_supports(): array
   {
      return $this->definition['supports'];
   }

   /**
    * Devuelve los argumentos finales para register_post_type().
    *
    * @return array<string, mixed>
    */
   public function get_args(): array
   {
      $args = $this->definition['args'];

      /*
       * Labels generados por FRW.
       *
       * Si el módulo declaró labels específicos dentro de args,
       * esos valores tienen prioridad.
       */
      $args['labels'] = array_replace(
         $this->build_labels(),
         is_array($args['labels'] ?? null)
         ? $args['labels']
         : []
      );

      $args['supports'] = $this->definition['supports'];

      /*
       * WordPress recomienda declarar también aquí las
       * taxonomías relacionadas con el CPT.
       */
      if ($this->definition['taxonomies'] !== []) {
         $args['taxonomies'] = $this->definition['taxonomies'];
      }

      return $args;
   }

   /**
    * Devuelve toda la definición normalizada.
    *
    * @return array<string, mixed>
    */
   public function to_array(): array
   {
      return [
         'slug' => $this->slug,
         'native' => $this->is_native(),
         'enabled' => $this->is_enabled(),
         'singular' => $this->get_singular(),
         'plural' => $this->get_plural(),
         'supports' => $this->get_supports(),
         'taxonomies' => $this->get_taxonomies(),
         'args' => $this->get_args(),
         'resources' => $this->get_resources(),
      ];
   }

   /**
    * Normaliza la definición recibida.
    *
    * @param array<string, mixed> $definition
    *
    * @return array<string, mixed>
    */
   private function normalize(
      array $definition
   ): array {
      $defaults = [
         /*
          * Los Post Types nativos no serán registrados
          * nuevamente por PostTypeRegistry.
          */
         'enabled' => true,
         'native' => false,

         /*
          * Etiquetas humanas.
          */
         'singular' => $this->humanize_slug(
            $this->slug
         ),

         'plural' => $this->humanize_slug(
            $this->slug
         ),

         /*
          * Características soportadas por WordPress.
          */
         'supports' => [
            'title',
         ],
         'resources' => [

         ],
         /*
          * Taxonomías que deben relacionarse con el CPT.
          */
         'taxonomies' => [],

         /*
          * Argumentos propios de register_post_type().
          */
         'args' => [
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'exclude_from_search' => false,
            'query_var' => true,
            'rewrite' => true,
            'map_meta_cap' => true,
         ],
      ];

      /*
       * array_replace_recursive es útil aquí para permitir:
       *
       * 'args' => [
       *    'public' => false,
       * ]
       *
       * sin eliminar el resto de defaults.
       */
      $definition = array_replace_recursive(
         $defaults,
         $definition
      );

      $definition['enabled'] = (bool) $definition['enabled'];

      $definition['native'] = (bool) $definition['native'];

      $definition['singular'] = sanitize_text_field(
         (string) $definition['singular']
      );

      $definition['plural'] = sanitize_text_field(
         (string) $definition['plural']
      );

      $definition['supports'] = $this->normalize_supports(
         $definition['supports']
      );

      $definition['taxonomies'] = $this->normalize_taxonomies(
         $definition['taxonomies']
      );

      if (!is_array($definition['args'])) {
         $definition['args'] = [];
      }
      if (!is_array($definition['resources'])) {
         $definition['resources'] = [];
      }

      return $definition;
   }

   /**
    * Indica si la definición está habilitada.
    */
   public function is_enabled(): bool
   {
      return (bool) $this->definition['enabled'];
   }

   /**
    * Valida la definición.
    */
   private function validate(): void
   {
      if ($this->slug === '') {
         throw new \InvalidArgumentException(
            'El Post Type debe declarar un slug válido.'
         );
      }

      /*
       * WordPress limita el identificador del Post Type
       * a un máximo de 20 caracteres.
       */
      if (strlen($this->slug) > 20) {
         throw new \InvalidArgumentException(
            sprintf(
               'El slug del Post Type "%s" no puede superar 20 caracteres.',
               $this->slug
            )
         );
      }

      if ($this->get_singular() === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'El Post Type "%s" debe declarar un nombre singular.',
               $this->slug
            )
         );
      }

      if ($this->get_plural() === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'El Post Type "%s" debe declarar un nombre plural.',
               $this->slug
            )
         );
      }
   }

   /**
    * Genera labels estándar de WordPress.
    *
    * @return array<string, string>
    */
   private function build_labels(): array
   {
      $singular = $this->get_singular();
      $plural = $this->get_plural();

      return [
         'name' => $plural,
         'singular_name' => $singular,

         'menu_name' => $plural,
         'name_admin_bar' => $singular,

         'add_new' => 'Añadir nuevo(a)',

         'add_new_item' => sprintf(
            'Añadir %s',
            $singular
         ),

         'new_item' => sprintf(
            'Nuevo(a) %s',
            $singular
         ),

         'edit_item' => sprintf(
            'Editar %s',
            $singular
         ),

         'view_item' => sprintf(
            'Ver %s',
            $singular
         ),

         'all_items' => $plural,

         'search_items' => sprintf(
            'Buscar %s',
            $plural
         ),

         'not_found' => sprintf(
            'No se encontraron %s.',
            strtolower($plural)
         ),

         'not_found_in_trash' => sprintf(
            'No se encontraron %s en la papelera.',
            strtolower($plural)
         ),

         'archives' => sprintf(
            'Archivo de %s',
            $plural
         ),

         'attributes' => sprintf(
            'Atributos de %s',
            $singular
         ),
      ];
   }

   /**
    * Normaliza el slug.
    */
   private function normalize_slug(
      string $slug
   ): string {
      return sanitize_key(
         trim($slug)
      );
   }

   /**
    * Convierte un slug a texto legible.
    */
   private function humanize_slug(
      string $slug
   ): string {
      $text = str_replace(
         ['_', '-'],
         ' ',
         $slug
      );

      return ucfirst($text);
   }

   /**
    * Normaliza supports.
    *
    * @return array<int|string, mixed>
    */
   private function normalize_supports(
      mixed $supports
   ): array {
      if (!is_array($supports)) {
         return [];
      }

      $normalized = [];

      foreach ($supports as $key => $value) {
         /*
          * Forma habitual:
          *
          * [
          *    'title',
          *    'editor',
          *    'thumbnail',
          * ]
          */
         if (is_int($key)) {
            if (is_string($value)) {
               $value = sanitize_key($value);

               if ($value !== '') {
                  $normalized[] = $value;
               }

               continue;
            }

            /*
             * Conserva estructuras especiales admitidas
             * por WordPress sin intentar convertirlas a string.
             */
            if (is_array($value)) {
               $normalized[] = $value;
            }

            continue;
         }

         /*
          * Forma asociativa excepcional.
          */
         if (is_string($key)) {
            $key = sanitize_key($key);

            if ($key !== '') {
               $normalized[$key] = $value;
            }
         }
      }

      return $normalized;
   }
   /**
    * Indica si el Post Type declara un recurso.
    */
   public function has_resource(
      string $resource
   ): bool {
      $resource = sanitize_key($resource);

      if ($resource === '') {
         return false;
      }

      return array_key_exists(
         $resource,
         $this->definition['resources']
      );
   }

   /**
    * Devuelve un recurso específico del Post Type.
    */
   public function get_resource(
      string $resource
   ): mixed {
      $resource = sanitize_key($resource);

      if ($resource === '') {
         return null;
      }

      return $this->definition['resources'][$resource]
         ?? null;
   }

   /**
    * Devuelve todos los recursos del Post Type.
    *
    * @return array<string, mixed>
    */
   public function get_resources(): array
   {
      return $this->definition['resources'];
   }
   /**
    * Normaliza taxonomías relacionadas.
    *
    * @return string[]
    */
   private function normalize_taxonomies(
      mixed $taxonomies
   ): array {
      if (!is_array($taxonomies)) {
         return [];
      }

      $normalized = [];

      foreach ($taxonomies as $taxonomy) {
         if (!is_string($taxonomy)) {
            continue;
         }

         $taxonomy = sanitize_key($taxonomy);

         if ($taxonomy !== '') {
            $normalized[] = $taxonomy;
         }
      }

      return array_values(
         array_unique($normalized)
      );
   }
}