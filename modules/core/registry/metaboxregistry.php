<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Registry;

use FWK\Modules\Core\Content\MetaBoxDefinition;
use FWK\Modules\Core\Content\PostTypeDefinition;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Registro central de Metaboxes de WP FRW.
 *
 * Responsabilidades:
 *
 * - cargar metaboxes declarados por los CPT;
 * - crear MetaBoxDefinition;
 * - validar campos contra MetaRegistry;
 * - registrar metaboxes en wp-admin;
 * - renderizar controles;
 * - guardar valores de metadata;
 * - mantener errores y advertencias para diagnóstico.
 *
 * @package FWK
 */
final class MetaBoxRegistry
{
   use Singleton;

   /**
    * Definiciones registradas.
    *
    * @var array<string, MetaBoxDefinition>
    */
   private array $definitions = [];

   /**
    * Módulo propietario de cada Metabox.
    *
    * @var array<string, string>
    */
   private array $owners = [];

   /**
    * Metaboxes registrados.
    *
    * @var string[]
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
    * Evita registrar hooks más de una vez.
    */
   private bool $booted = false;

   protected function __construct()
   {
   }

   /**
    * Incorpora los metaboxes declarados
    * por los CPT de un módulo.
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
         } catch (\InvalidArgumentException $exception) {
            $this->errors[] = sprintf(
               'No fue posible procesar los metaboxes del Post Type "%s": %s',
               $postType,
               $exception->getMessage()
            );

            continue;
         }

         if (
            !$postTypeDefinition
               ->has_resource('metaboxes')
         ) {
            continue;
         }

         $resource = $postTypeDefinition
            ->get_resource('metaboxes');

         $definitions =
            $this->load_metabox_resource(
               $module,
               $postType,
               $resource
            );

         foreach ($definitions as $id => $definition) {
            if (
               !is_string($id)
               || !is_array($definition)
            ) {
               continue;
            }

            /*
             * Si el archivo pertenece a un CPT concreto,
             * permitimos omitir post_types y lo inferimos.
             */
            if (
               !isset($definition['post_types'])
               || !is_array(
                  $definition['post_types']
               )
               || $definition['post_types'] === []
            ) {
               $definition['post_types'] = [
                  $postType,
               ];
            }

            try {
               $metaBox =
                  new MetaBoxDefinition(
                     $id,
                     $definition
                  );
            } catch (\InvalidArgumentException $exception) {
               $this->errors[] = sprintf(
                  'Metabox "%s" del Post Type "%s": %s',
                  $id,
                  $postType,
                  $exception->getMessage()
               );

               continue;
            }

            $this->register(
               $module,
               $metaBox
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
      MetaBoxDefinition $definition
   ): self {
      $id = $definition->get_id();

      if (isset($this->definitions[$id])) {
         $existingOwner =
            $this->owners[$id]
            ?? 'desconocido';

         if (
            $existingOwner
            !== $module->get_slug()
         ) {
            $this->errors[] = sprintf(
               'El Metabox "%s" fue declarado por los módulos "%s" y "%s".',
               $id,
               $existingOwner,
               $module->get_slug()
            );
         }

         return $this;
      }

      $this->definitions[$id] =
         $definition;

      $this->owners[$id] =
         $module->get_slug();

      $this->validate_fields(
         $definition
      );

      return $this;
   }

   /**
    * Registra hooks de administración.
    */
   public function boot(): void
   {
      if ($this->booted) {
         return;
      }

      add_action(
         'add_meta_boxes',
         [$this, 'register_meta_boxes']
      );

      add_action(
         'save_post',
         [$this, 'save_post'],
         10,
         2
      );

      $this->booted = true;
   }

   /**
    * Materializa los Metaboxes.
    */
   public function register_meta_boxes(): void
   {
      foreach (
         $this->definitions
         as $id => $definition
      ) {
         if (!$definition->is_enabled()) {
            continue;
         }

         add_meta_box(
            $id,
            $definition->get_title(),
            [$this, 'render_meta_box'],
            $definition->get_post_types(),
            $definition->get_context(),
            $definition->get_priority(),
            [
               'fwk_metabox_id' => $id,
            ]
         );

         $this->registered[] = $id;
      }

      $this->registered = array_values(
         array_unique($this->registered)
      );
   }

   /**
    * Renderiza un Metabox.
    *
    * @param \WP_Post $post
    * @param array<string, mixed> $box
    */
   public function render_meta_box(
      \WP_Post $post,
      array $box
   ): void {
      $id = (string) (
         $box['args']['fwk_metabox_id']
         ?? ''
      );

      $definition =
         $this->definitions[$id]
         ?? null;

      if (
         !$definition
         instanceof MetaBoxDefinition
      ) {
         return;
      }

      wp_nonce_field(
         'fwk_save_metabox_' . $id,
         'fwk_metabox_nonce_' . $id
      );

      foreach (
         $definition->get_fields()
         as $key => $field
      ) {
         $value = get_post_meta(
            $post->ID,
            $key,
            true
         );

         $this->render_field(
            $key,
            $field,
            $value
         );
      }
   }

   /**
    * Renderiza un campo individual.
    *
    * @param array<string, mixed> $field
    */
   private function render_field(
      string $key,
      array $field,
      mixed $value
   ): void {
      $type = (string) (
         $field['type']
         ?? 'text'
      );

      $label = (string) (
         $field['label']
         ?? $key
      );

      $description = (string) (
         $field['description']
         ?? ''
      );

      ?>
      <div class="fwk-metabox-field">

         <p>
            <label for="<?= esc_attr($key); ?>">
               <strong>
                  <?= esc_html($label); ?>
               </strong>
            </label>
         </p>

         <?php
         switch ($type) {
            case 'select':
               $this->render_select_field(
                  $key,
                  $field,
                  $value
               );
               break;

            case 'textarea':
               $this->render_textarea_field(
                  $key,
                  $value
               );
               break;

            case 'number':
               $this->render_input_field(
                  $key,
                  'number',
                  $value
               );
               break;

            case 'checkbox':
               $this->render_checkbox_field(
                  $key,
                  $value
               );
               break;

            case 'text':
            default:
               $this->render_input_field(
                  $key,
                  'text',
                  $value
               );
               break;
         }
         ?>

         <?php if ($description !== ''): ?>
            <p class="description">
               <?= esc_html($description); ?>
            </p>
         <?php endif; ?>

      </div>
      <?php
   }

   /**
    * Renderiza input text/number.
    */
   private function render_input_field(
      string $key,
      string $type,
      mixed $value
   ): void {
      ?>
      <input type="<?= esc_attr($type); ?>" id="<?= esc_attr($key); ?>" name="<?= esc_attr($key); ?>"
         value="<?= esc_attr((string) $value); ?>" class="widefat" <?= $type === 'number'
               ? 'step="any"'
               : ''; ?>>
      <?php
   }
   /**
    * Renderiza textarea.
    */
   private function render_textarea_field(
      string $key,
      mixed $value
   ): void {
      ?>
      <textarea id="<?= esc_attr($key); ?>" name="<?= esc_attr($key); ?>" class="widefat" rows="4"><?= esc_textarea(
             (string) $value
          ); ?></textarea>
      <?php
   }

   /**
    * Renderiza select.
    *
    * @param array<string, mixed> $field
    */
   private function render_select_field(
      string $key,
      array $field,
      mixed $value
   ): void {
      $options = $field['options'] ?? [];

      if (!is_array($options)) {
         $options = [];
      }

      ?>
      <select id="<?= esc_attr($key); ?>" name="<?= esc_attr($key); ?>" class="widefat">

         <?php foreach (
            $options
            as $optionValue => $optionLabel
         ): ?>

            <option value="<?= esc_attr(
               (string) $optionValue
            ); ?>" <?= selected(
                (string) $value,
                (string) $optionValue,
                false
             ); ?>>
               <?= esc_html(
                  (string) $optionLabel
               ); ?>
            </option>

         <?php endforeach; ?>

      </select>
      <?php
   }

   /**
    * Renderiza checkbox.
    */
   private function render_checkbox_field(
      string $key,
      mixed $value
   ): void {
      ?>
      <label>
         <input type="checkbox" id="<?= esc_attr($key); ?>" name="<?= esc_attr($key); ?>" value="1" <?= checked(
                (bool) $value,
                true,
                false
             ); ?>>
         <?= esc_html__(
            'Sí',
            'FWK'
         ); ?>
      </label>
      <?php
   }

   /**
    * Guarda los campos de los Metaboxes.
    */
   public function save_post(
      int $postId,
      \WP_Post $post
   ): void {
      /*
       * Ignora autosaves y revisiones.
       */
      if (
         wp_is_post_autosave($postId)
         || wp_is_post_revision($postId)
      ) {
         return;
      }

      /*
       * Valida capacidad sobre el objeto concreto.
       */
      if (
         !current_user_can(
            'edit_post',
            $postId
         )
      ) {
         return;
      }

      foreach (
         $this->definitions
         as $id => $definition
      ) {
         if (!$definition->is_enabled()) {
            continue;
         }

         if (
            !in_array(
               $post->post_type,
               $definition->get_post_types(),
               true
            )
         ) {
            continue;
         }

         $nonceName =
            'fwk_metabox_nonce_' . $id;

         if (
            !isset($_POST[$nonceName])
            || !is_string(
               $_POST[$nonceName]
            )
         ) {
            continue;
         }

         $nonce = sanitize_text_field(
            wp_unslash(
               $_POST[$nonceName]
            )
         );

         if (
            !wp_verify_nonce(
               $nonce,
               'fwk_save_metabox_' . $id
            )
         ) {
            continue;
         }

         $this->save_fields(
            $postId,
            $definition
         );
      }
   }

   /**
    * Guarda los campos de una definición.
    */
   private function save_fields(
      int $postId,
      MetaBoxDefinition $metaBox
   ): void {
      $metaRegistry =
         MetaRegistry::get_instance();

      foreach (
         $metaBox->get_fields()
         as $key => $field
      ) {
         /*
          * El Metabox solo puede guardar metas
          * registrados en MetaRegistry.
          */
         if (
            !$metaRegistry->has(
               get_post_type($postId),
               $key
            )
         ) {
            continue;
         }

         $type = (string) (
            $field['type']
            ?? 'text'
         );

         /*
          * Los checkboxes ausentes significan false.
          */
         if (
            $type === 'checkbox'
            && !isset($_POST[$key])
         ) {
            update_post_meta(
               $postId,
               $key,
               0
            );

            continue;
         }

         if (!isset($_POST[$key])) {
            continue;
         }

         $rawValue = wp_unslash(
            $_POST[$key]
         );

         /*
          * La sanitización final corresponde a
          * MetaDefinition / registro de metadata.
          *
          * Aquí evitamos interpretar el significado
          * funcional del dato.
          */
         update_post_meta(
            $postId,
            $key,
            $rawValue
         );
      }
   }

   /**
    * Comprueba que todos los campos del Metabox
    * correspondan a metadata conocida.
    */
   private function validate_fields(
      MetaBoxDefinition $definition
   ): void {
      $metaRegistry =
         MetaRegistry::get_instance();

      foreach (
         $definition->get_post_types()
         as $postType
      ) {
         foreach (
            $definition->get_fields()
            as $key => $field
         ) {
            if (
               !$metaRegistry->has(
                  $postType,
                  $key
               )
            ) {
               $this->warnings[] = sprintf(
                  'El campo "%s" del Metabox "%s" no corresponde a metadata registrada para el Post Type "%s".',
                  $key,
                  $definition->get_id(),
                  $postType
               );
            }
         }
      }

      $this->warnings = array_values(
         array_unique(
            $this->warnings
         )
      );
   }

   /**
    * Carga el archivo de Metaboxes de un CPT.
    *
    * @return array<string, mixed>
    */
   private function load_metabox_resource(
      ModuleInterface $module,
      string $postType,
      mixed $resource
   ): array {
      if (is_array($resource)) {
         return $resource;
      }

      if (!is_string($resource)) {
         $this->errors[] = sprintf(
            'El recurso metaboxes del Post Type "%s" debe ser un arreglo o una ruta.',
            $postType
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
            'El archivo de Metaboxes del Post Type "%s" no existe: %s',
            $postType,
            $file
         );

         return [];
      }

      $definitions = require $file;

      if (!is_array($definitions)) {
         $this->errors[] = sprintf(
            'El archivo de Metaboxes "%s" debe devolver un arreglo.',
            $file
         );

         return [];
      }

      return $definitions;
   }

   /**
    * Devuelve todas las definiciones.
    *
    * @return array<string, MetaBoxDefinition>
    */
   public function all(): array
   {
      return $this->definitions;
   }

   /**
    * Devuelve los Metaboxes registrados.
    *
    * @return string[]
    */
   public function get_registered(): array
   {
      return $this->registered;
   }

   /**
    * Devuelve el propietario.
    */
   public function get_owner(
      string $id
   ): ?string {
      return $this->owners[
         sanitize_key($id)
      ] ?? null;
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
    * Indica si el Registry inició.
    */
   public function is_booted(): bool
   {
      return $this->booted;
   }

}