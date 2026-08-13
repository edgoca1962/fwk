<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Content;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Representa y normaliza la definición de un Post Meta.
 *
 * MetaDefinition no registra directamente metadata.
 * Únicamente:
 *
 * - valida la clave;
 * - identifica el Post Type propietario;
 * - normaliza el tipo;
 * - normaliza single/default;
 * - conserva callbacks de sanitización y autorización;
 * - prepara los argumentos para register_post_meta().
 *
 * @package FWK
 */
final class MetaDefinition
{
   /**
    * Post Type al que pertenece el meta.
    */
   private string $postType;

   /**
    * Clave del meta.
    */
   private string $key;

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
      string $postType,
      string $key,
      array $definition = []
   ) {
      $this->postType = $this->normalize_post_type(
         $postType
      );

      $this->key = $this->normalize_key(
         $key
      );

      $this->definition = $this->normalize(
         $definition
      );

      $this->validate();
   }

   /**
    * Devuelve el Post Type propietario.
    */
   public function get_post_type(): string
   {
      return $this->postType;
   }

   /**
    * Devuelve la clave de metadata.
    */
   public function get_key(): string
   {
      return $this->key;
   }

   /**
    * Indica si el meta está habilitado.
    */
   public function is_enabled(): bool
   {
      return (bool) $this->definition['enabled'];
   }

   /**
    * Devuelve el tipo.
    */
   public function get_type(): string
   {
      return (string) $this->definition['type'];
   }

   /**
    * Indica si almacena un único valor.
    */
   public function is_single(): bool
   {
      return (bool) $this->definition['single'];
   }

   /**
    * Devuelve el valor predeterminado.
    */
   public function get_default(): mixed
   {
      return $this->definition['default'];
   }

   /**
    * Indica si se expone en REST.
    */
   public function show_in_rest(): bool|array
   {
      $value = $this->definition['show_in_rest'];

      return is_array($value)
         ? $value
         : (bool) $value;
   }

   /**
    * Devuelve los argumentos para register_post_meta().
    *
    * @return array<string, mixed>
    */
   public function get_args(): array
   {
      $args = [
         'type' => $this->get_type(),

         'label' => $this->definition['label'],

         'description' =>
            $this->definition['description'],

         'single' => $this->is_single(),

         'default' => $this->get_default(),

         'show_in_rest' =>
            $this->show_in_rest(),
      ];

      if (
         is_callable(
            $this->definition['sanitize_callback']
         )
      ) {
         $args['sanitize_callback'] =
            $this->definition['sanitize_callback'];
      }

      if (
         is_callable(
            $this->definition['auth_callback']
         )
      ) {
         $args['auth_callback'] =
            $this->definition['auth_callback'];
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
         'post_type' => $this->get_post_type(),
         'key' => $this->get_key(),
         'enabled' => $this->is_enabled(),
         'type' => $this->get_type(),
         'single' => $this->is_single(),
         'default' => $this->get_default(),
         'show_in_rest' => $this->show_in_rest(),
         'args' => $this->get_args(),
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

         'type' => 'string',

         'label' => '',

         'description' => '',

         'single' => true,

         'default' => '',

         'show_in_rest' => false,

         'sanitize_callback' => null,

         'auth_callback' => null,
      ];

      $definition = array_replace(
         $defaults,
         $definition
      );

      $definition['enabled'] =
         (bool) $definition['enabled'];

      $definition['type'] =
         sanitize_key(
            (string) $definition['type']
         );

      $definition['label'] =
         sanitize_text_field(
            (string) $definition['label']
         );

      $definition['description'] =
         sanitize_text_field(
            (string) $definition['description']
         );

      $definition['single'] =
         (bool) $definition['single'];

      /*
       * show_in_rest puede ser:
       *
       * true
       * false
       * array con schema REST.
       */
      if (!is_array($definition['show_in_rest'])) {
         $definition['show_in_rest'] =
            (bool) $definition['show_in_rest'];
      }

      return $definition;
   }

   /**
    * Valida la definición.
    */
   private function validate(): void
   {
      if ($this->postType === '') {
         throw new \InvalidArgumentException(
            'MetaDefinition debe declarar un Post Type válido.'
         );
      }

      if ($this->key === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'El Post Type "%s" contiene una clave meta inválida.',
               $this->postType
            )
         );
      }

      $validTypes = [
         'string',
         'boolean',
         'integer',
         'number',
         'array',
         'object',
      ];

      if (
         !in_array(
            $this->get_type(),
            $validTypes,
            true
         )
      ) {
         throw new \InvalidArgumentException(
            sprintf(
               'El meta "%s" del Post Type "%s" declara el tipo inválido "%s".',
               $this->key,
               $this->postType,
               $this->get_type()
            )
         );
      }
   }

   /**
    * Normaliza el Post Type.
    */
   private function normalize_post_type(
      string $postType
   ): string {
      return sanitize_key(
         trim($postType)
      );
   }

   /**
    * Normaliza la clave meta.
    *
    * Conservamos "_" porque utilizaremos claves internas
    * como "_moneda".
    */
   private function normalize_key(
      string $key
   ): string {
      return sanitize_key(
         trim($key)
      );
   }
}