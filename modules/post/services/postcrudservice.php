<?php

declare(strict_types=1);

namespace FWK\Modules\Post\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Gestiona las operaciones CRUD
 * de los artículos del módulo Post.
 */
final class PostCrudService
{
   /**
    * Crea un nuevo artículo.
    *
    * @param array<string,mixed> $data
    *
    * @return int|\WP_Error
    */
   public function create(
      array $data
   ): int|\WP_Error {

      $title = sanitize_text_field(
         (string) (
            $data['title']
            ?? ''
         )
      );

      $content = wp_kses_post(
         (string) (
            $data['content']
            ?? ''
         )
      );

      if ($title === '') {
         return new \WP_Error(
            'fwk_post_title_required',
            __(
               'El título del artículo es obligatorio.',
               'FWK'
            )
         );
      }

      $postId = wp_insert_post(
         [
            'post_type' =>
               'post',

            'post_status' =>
               'publish',

            'post_title' =>
               $title,

            'post_content' =>
               $content,

            'post_author' =>
               get_current_user_id(),
         ],
         true
      );

      if (is_wp_error($postId)) {
         return $postId;
      }

      $imageResult =
         $this->handle_featured_image(
            $postId
         );

      if (is_wp_error($imageResult)) {

         /*
          * El artículo ya fue creado.
          * Por ahora devolvemos el error
          * para poder detectarlo.
          */
         return $imageResult;
      }

      return $postId;
   }

   /**
    * Procesa y asigna la imagen destacada.
    *
    * @return int|null|\WP_Error
    */
   private function handle_featured_image(
      int $postId
   ): int|null|\WP_Error {

      if (
         !isset($_FILES['featured_image'])
         || !is_array($_FILES['featured_image'])
      ) {
         return null;
      }

      $file =
         $_FILES['featured_image'];

      $error =
         (int) (
            $file['error']
            ?? UPLOAD_ERR_NO_FILE
         );

      if ($error === UPLOAD_ERR_NO_FILE) {
         return null;
      }

      if ($error !== UPLOAD_ERR_OK) {
         return new \WP_Error(
            'fwk_featured_image_upload_error',
            __(
               'No se pudo cargar la imagen destacada.',
               'FWK'
            )
         );
      }

      /*
       * Estas funciones no siempre están
       * cargadas en peticiones front-end.
       */
      require_once ABSPATH
         . 'wp-admin/includes/file.php';

      require_once ABSPATH
         . 'wp-admin/includes/media.php';

      require_once ABSPATH
         . 'wp-admin/includes/image.php';

      $attachmentId =
         media_handle_upload(
            'featured_image',
            $postId
         );

      if (is_wp_error($attachmentId)) {
         return $attachmentId;
      }

      set_post_thumbnail(
         $postId,
         $attachmentId
      );

      return $attachmentId;
   }

   public function update(
      int $postId,
      array $data
   ): int|\WP_Error {

      $post =
         get_post(
            $postId
         );

      if (
         !$post instanceof \WP_Post
         || $post->post_type !== 'post'
      ) {
         return new \WP_Error(
            'fwk_post_not_found',
            __(
               'El artículo solicitado no existe.',
               'FWK'
            )
         );
      }

      $title =
         sanitize_text_field(
            wp_unslash(
               (string) (
                  $data['title']
                  ?? ''
               )
            )
         );

      $content =
         wp_unslash(
            (string) (
               $data['content']
               ?? ''
            )
         );

      if ($title === '') {
         return new \WP_Error(
            'fwk_post_title_required',
            __(
               'El título del artículo es obligatorio.',
               'FWK'
            )
         );
      }

      $result =
         wp_update_post(
            [
               'ID' =>
                  $postId,

               'post_title' =>
                  $title,

               'post_content' =>
                  $content,
            ],
            true
         );

      if (is_wp_error($result)) {
         return $result;
      }

      $imageResult =
         $this->handle_featured_image(
            $postId
         );

      if (is_wp_error($imageResult)) {
         return $imageResult;
      }

      return $postId;
   }

   public function delete(
      int $postId
   ): bool|\WP_Error {

      $post =
         get_post(
            $postId
         );

      if (
         !$post instanceof \WP_Post
         || $post->post_type !== 'post'
      ) {
         return new \WP_Error(
            'fwk_post_not_found',
            __(
               'El artículo solicitado no existe.',
               'FWK'
            )
         );
      }

      $result =
         wp_trash_post(
            $postId
         );

      if (
         !$result instanceof \WP_Post
      ) {
         return new \WP_Error(
            'fwk_post_delete_failed',
            __(
               'No se pudo eliminar el artículo.',
               'FWK'
            )
         );
      }

      return true;
   }
}