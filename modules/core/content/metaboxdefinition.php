<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Content;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Representa y normaliza la definición de un Metabox.
 *
 * MetaBoxDefinition no registra metaboxes en WordPress.
 *
 * Responsabilidades:
 *
 * - validar el identificador;
 * - validar los Post Types asociados;
 * - normalizar contexto y prioridad;
 * - definir los campos que mostrará el metabox;
 * - preparar la información para MetaBoxRegistry.
 *
 * @package FWK
 */
final class MetaBoxDefinition
{
   /**
    * Identificador único del metabox.
    */
   private string $id;

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
      string $id,
      array $definition = []
   ) {
      $this->id = $this->normalize_id($id);

      $this->definition = $this->normalize(
         $definition
      );

      $this->validate();
   }

   /**
    * Devuelve el ID del metabox.
    */
   public function get_id(): string
   {
      return $this->id;
   }

   /**
    * Indica si está habilitado.
    */
   public function is_enabled(): bool
   {
      return (bool) $this->definition['enabled'];
   }

   /**
    * Devuelve el título visible.
    */
   public function get_title(): string
   {
      return (string) $this->definition['title'];
   }

   /**
    * Devuelve los Post Types asociados.
    *
    * @return string[]
    */
   public function get_post_types(): array
   {
      return $this->definition['post_types'];
   }

   /**
    * Devuelve el contexto del metabox.
    */
   public function get_context(): string
   {
      return (string) $this->definition['context'];
   }

   /**
    * Devuelve la prioridad.
    */
   public function get_priority(): string
   {
      return (string) $this->definition['priority'];
   }

   /**
    * Devuelve los campos declarados.
    *
    * @return array<string, array<string, mixed>>
    */
   public function get_fields(): array
   {
      return $this->definition['fields'];
   }

   /**
    * Indica si existe un campo.
    */
   public function has_field(
      string $key
   ): bool {
      $key = sanitize_key($key);

      return isset(
         $this->definition['fields'][$key]
      );
   }

   /**
    * Devuelve la configuración de un campo.
    *
    * @return array<string, mixed>|null
    */
   public function get_field(
      string $key
   ): ?array {
      $key = sanitize_key($key);

      return $this->definition['fields'][$key]
         ?? null;
   }

   /**
    * Devuelve toda la definición normalizada.
    *
    * @return array<string, mixed>
    */
   public function to_array(): array
   {
      return [
         'id' => $this->get_id(),
         'enabled' => $this->is_enabled(),
         'title' => $this->get_title(),
         'post_types' => $this->get_post_types(),
         'context' => $this->get_context(),
         'priority' => $this->get_priority(),
         'fields' => $this->get_fields(),
      ];
   }

   /**
    * Normaliza la definición.
    *
    * @param array<string, mixed> $definition
    *
    * @return array<string, mixed>
    */
   private function normalize(
      array $definition
   ): array {
      $defaults = [
         'enabled' => true,

         'title' => '',

         'post_types' => [],

         /*
          * Valores compatibles con add_meta_box().
          */
         'context' => 'normal',

         'priority' => 'default',

         /*
          * Cada campo referencia normalmente
          * un meta registrado mediante MetaRegistry.
          */
         'fields' => [],
      ];

      $definition = array_replace(
         $defaults,
         $definition
      );

      $definition['enabled'] =
         (bool) $definition['enabled'];

      $definition['title'] =
         sanitize_text_field(
            (string) $definition['title']
         );

      $definition['post_types'] =
         $this->normalize_post_types(
            $definition['post_types']
         );

      $definition['context'] =
         sanitize_key(
            (string) $definition['context']
         );

      $definition['priority'] =
         sanitize_key(
            (string) $definition['priority']
         );

      $definition['fields'] =
         $this->normalize_fields(
            $definition['fields']
         );

      return $definition;
   }

   /**
    * Valida la definición.
    */
   private function validate(): void
   {
      if ($this->id === '') {
         throw new \InvalidArgumentException(
            'El Metabox debe declarar un ID válido.'
         );
      }

      if ($this->get_title() === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'El Metabox "%s" debe declarar un título.',
               $this->id
            )
         );
      }

      if ($this->get_post_types() === []) {
         throw new \InvalidArgumentException(
            sprintf(
               'El Metabox "%s" debe asociarse al menos a un Post Type.',
               $this->id
            )
         );
      }

      $validContexts = [
         'normal',
         'side',
         'advanced',
      ];

      if (
         !in_array(
            $this->get_context(),
            $validContexts,
            true
         )
      ) {
         throw new \InvalidArgumentException(
            sprintf(
               'El Metabox "%s" declara el contexto inválido "%s".',
               $this->id,
               $this->get_context()
            )
         );
      }

      $validPriorities = [
         'high',
         'core',
         'default',
         'low',
      ];

      if (
         !in_array(
            $this->get_priority(),
            $validPriorities,
            true
         )
      ) {
         throw new \InvalidArgumentException(
            sprintf(
               'El Metabox "%s" declara la prioridad inválida "%s".',
               $this->id,
               $this->get_priority()
            )
         );
      }

      if ($this->get_fields() === []) {
         throw new \InvalidArgumentException(
            sprintf(
               'El Metabox "%s" debe declarar al menos un campo.',
               $this->id
            )
         );
      }
   }

   /**
    * Normaliza el ID.
    */
   private function normalize_id(
      string $id
   ): string {
      return sanitize_key(
         trim($id)
      );
   }

   /**
    * Normaliza los Post Types.
    *
    * @return string[]
    */
   private function normalize_post_types(
      mixed $postTypes
   ): array {
      if (!is_array($postTypes)) {
         return [];
      }

      $normalized = [];

      foreach ($postTypes as $postType) {
         if (!is_string($postType)) {
            continue;
         }

         $postType = sanitize_key(
            $postType
         );

         if ($postType !== '') {
            $normalized[] = $postType;
         }
      }

      return array_values(
         array_unique($normalized)
      );
   }

   /**
    * Normaliza los campos.
    *
    * En esta primera versión solamente conservamos
    * configuración de presentación.
    *
    * La definición real del dato continúa viviendo
    * en MetaDefinition.
    *
    * @return array<string, array<string, mixed>>
    */
   private function normalize_fields(
      mixed $fields
   ): array {
      if (!is_array($fields)) {
         return [];
      }

      $normalized = [];

      foreach ($fields as $key => $field) {
         if (
            !is_string($key)
            || !is_array($field)
         ) {
            continue;
         }

         $key = sanitize_key($key);

         if ($key === '') {
            continue;
         }

         $defaults = [
            'type' => 'text',
            'label' => '',
            'description' => '',
            'options' => [],
         ];

         $field = array_replace(
            $defaults,
            $field
         );

         $field['type'] = sanitize_key(
            (string) $field['type']
         );

         $field['label'] =
            sanitize_text_field(
               (string) $field['label']
            );

         $field['description'] =
            sanitize_text_field(
               (string) $field['description']
            );

         if (!is_array($field['options'])) {
            $field['options'] = [];
         }

         $normalized[$key] = $field;
      }

      return $normalized;
   }
}