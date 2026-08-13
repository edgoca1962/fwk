<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Content;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Representa y normaliza la definición de una taxonomía.
 *
 * Esta clase:
 *
 * - valida el slug;
 * - aplica valores predeterminados;
 * - genera labels básicos;
 * - permite asociar uno o varios object types;
 * - distingue taxonomías nativas y personalizadas;
 * - prepara los argumentos para TaxonomyRegistry.
 *
 * No registra directamente la taxonomía.
 *
 * @package FWK
 */
final class TaxonomyDefinition
{
   /**
    * Identificador de la taxonomía.
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
    * Devuelve el slug.
    */
   public function get_slug(): string
   {
      return $this->slug;
   }

   /**
    * Indica si la taxonomía está habilitada.
    */
   public function is_enabled(): bool
   {
      return (bool) $this->definition['enabled'];
   }

   /**
    * Indica si es una taxonomía nativa.
    */
   public function is_native(): bool
   {
      return (bool) $this->definition['native'];
   }

   /**
    * Indica si la taxonomía es jerárquica.
    */
   public function is_hierarchical(): bool
   {
      return (bool) $this->definition['hierarchical'];
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
    * Devuelve los object types asociados.
    *
    * @return string[]
    */
   public function get_object_types(): array
   {
      return $this->definition['object_types'];
   }

   /**
    * Devuelve los argumentos finales para register_taxonomy().
    *
    * @return array<string, mixed>
    */
   public function get_args(): array
   {
      $args = $this->definition['args'];

      /*
       * La propiedad hierarchical forma parte de la definición
       * principal porque condiciona también los labels y el UI.
       */
      $args['hierarchical'] = $this->is_hierarchical();

      /*
       * Los labels particulares declarados por el módulo
       * sobrescriben únicamente los defaults necesarios.
       */
      $args['labels'] = array_replace(
         $this->build_labels(),
         is_array($args['labels'] ?? null)
         ? $args['labels']
         : []
      );

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
         'enabled' => $this->is_enabled(),
         'native' => $this->is_native(),
         'singular' => $this->get_singular(),
         'plural' => $this->get_plural(),
         'hierarchical' => $this->is_hierarchical(),
         'object_types' => $this->get_object_types(),
         'args' => $this->get_args(),
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
          * Permite declarar recursos que aún no deben
          * materializarse.
          */
         'enabled' => true,

         /*
          * Las taxonomías nativas no serán registradas
          * nuevamente por TaxonomyRegistry.
          */
         'native' => false,

         /*
          * Nombre humano.
          */
         'singular' => $this->humanize_slug(
            $this->slug
         ),

         'plural' => $this->humanize_slug(
            $this->slug
         ),

         /*
          * false = comportamiento tipo tags.
          * true  = comportamiento tipo categorías.
          */
         'hierarchical' => false,

         /*
          * CPT u otros object types asociados.
          */
         'object_types' => [],

         /*
          * Argumentos propios de register_taxonomy().
          */
         'args' => [
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => true,
         ],
      ];

      $definition = array_replace_recursive(
         $defaults,
         $definition
      );

      $definition['enabled'] = (bool) $definition['enabled'];

      $definition['native'] = (bool) $definition['native'];

      $definition['hierarchical'] = (bool) $definition['hierarchical'];

      $definition['singular'] = sanitize_text_field(
         (string) $definition['singular']
      );

      $definition['plural'] = sanitize_text_field(
         (string) $definition['plural']
      );

      $definition['object_types'] = $this->normalize_object_types(
         $definition['object_types']
      );

      if (!is_array($definition['args'])) {
         $definition['args'] = [];
      }

      return $definition;
   }

   /**
    * Valida la definición.
    */
   private function validate(): void
   {
      if ($this->slug === '') {
         throw new \InvalidArgumentException(
            'La taxonomía debe declarar un slug válido.'
         );
      }

      /*
       * WordPress limita el nombre interno de la taxonomía
       * a 32 caracteres.
       */
      if (strlen($this->slug) > 32) {
         throw new \InvalidArgumentException(
            sprintf(
               'El slug de la taxonomía "%s" no puede superar 32 caracteres.',
               $this->slug
            )
         );
      }

      if ($this->get_singular() === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'La taxonomía "%s" debe declarar un nombre singular.',
               $this->slug
            )
         );
      }

      if ($this->get_plural() === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'La taxonomía "%s" debe declarar un nombre plural.',
               $this->slug
            )
         );
      }

      /*
       * Una taxonomía personalizada habilitada necesita al
       * menos un object type para poder ser materializada.
       *
       * Las nativas pueden declararse únicamente como
       * referencia del módulo.
       */
      if (
         $this->is_enabled()
         && !$this->is_native()
         && $this->get_object_types() === []
      ) {
         throw new \InvalidArgumentException(
            sprintf(
               'La taxonomía "%s" debe asociarse al menos a un object type.',
               $this->slug
            )
         );
      }
   }

   /**
    * Genera labels estándar.
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

         'search_items' => sprintf(
            'Buscar %s',
            $plural
         ),

         'all_items' => sprintf(
            'Todos(as) los(as) %s',
            $plural
         ),

         'edit_item' => sprintf(
            'Editar %s',
            $singular
         ),

         'view_item' => sprintf(
            'Ver %s',
            $singular
         ),

         'update_item' => sprintf(
            'Actualizar %s',
            $singular
         ),

         'add_new_item' => sprintf(
            'Añadir nuevo(a) %s',
            $singular
         ),

         'new_item_name' => sprintf(
            'Nuevo(a) %s',
            $singular
         ),

         'not_found' => sprintf(
            'No se encontraron %s.',
            strtolower($plural)
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
    * Convierte un slug en texto legible.
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
    * Normaliza object types.
    *
    * @return string[]
    */
   private function normalize_object_types(
      mixed $objectTypes
   ): array {
      if (!is_array($objectTypes)) {
         return [];
      }

      $objectTypes = array_map(
         static fn(mixed $objectType): string =>
         sanitize_key((string) $objectType),
         $objectTypes
      );

      return array_values(
         array_unique(
            array_filter($objectTypes)
         )
      );
   }
}