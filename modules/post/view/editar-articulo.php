<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Post\Services\PostViewService;

$postId =
   isset($_GET['post_id'])
   ? absint($_GET['post_id'])
   : 0;

$pageData =
   (new PostViewService())
      ->prepare_edit_form(
         $postId
      );

?>

<section class="container py-5">

   <?php if (
      !($pageData['valid'] ?? false)
   ): ?>

      <div class="alert alert-danger">
         <?= esc_html__(
            'El artículo solicitado no existe.',
            'FWK'
         ); ?>
      </div>

   <?php elseif (
      !($pageData['authorized'] ?? false)
   ): ?>

      <div class="alert alert-danger">
         <?= esc_html__(
            'No tienes autorización para editar este artículo.',
            'FWK'
         ); ?>
      </div>

   <?php else: ?>

      <h1 class="mb-4">
         <?= esc_html(
            $pageData['title']
            ?? ''
         ); ?>
      </h1>

      <form method="post" action="<?= esc_url(
         $pageData['form_action']
         ?? ''
      ); ?>" enctype="multipart/form-data">

         <input type="hidden" name="action" value="<?= esc_attr(
            $pageData['action']
            ?? ''
         ); ?>">

         <input type="hidden" name="post_id" value="<?= esc_attr(
            (string) $postId
         ); ?>">

         <?php
         wp_nonce_field(
            $pageData['nonce_action'],
            $pageData['nonce_name']
         );
         ?>

         <div class="mb-3">

            <label for="post-title" class="form-label">
               <?= esc_html(
                  $pageData['fields']['title']['label']
                  ?? ''
               ); ?>
            </label>

            <input type="text" id="post-title" name="<?= esc_attr(
               $pageData['fields']['title']['name']
               ?? 'title'
            ); ?>" value="<?= esc_attr(
                $pageData['fields']['title']['value']
                ?? ''
             ); ?>" class="form-control" required>

         </div>

         <div class="mb-3">

            <label class="form-label">
               <?= esc_html(
                  $pageData['fields']['content']['label']
                  ?? ''
               ); ?>
            </label>

            <?php

            wp_editor(
               $pageData['fields']['content']['value']
               ?? '',
               'post-content',
               [
                  'textarea_name' =>
                     $pageData['fields']['content']['name']
                     ?? 'content',

                  'textarea_rows' =>
                     12,

                  'media_buttons' =>
                     true,

                  'teeny' =>
                     false,
               ]
            );

            ?>

         </div>
         <div class="mb-4">

            <label for="featured-image" class="form-label">
               <?= esc_html(
                  $pageData['featured_image']['label']
                  ?? ''
               ); ?>
            </label>

            <?php if (
               (
                  $pageData['featured_image']['url']
                  ?? ''
               ) !== ''
            ): ?>

               <div class="mb-3">

                  <img src="<?= esc_url(
                     $pageData['featured_image']['url']
                  ); ?>" alt="" class="img-fluid rounded" style="max-height: 300px;">

               </div>

               <div class="form-text mb-2">
                  <?= esc_html__(
                     'Selecciona una nueva imagen para sustituir la imagen destacada actual.',
                     'FWK'
                  ); ?>
               </div>

            <?php endif; ?>

            <input type="file" id="featured-image" name="<?= esc_attr(
               $pageData['featured_image']['name']
               ?? 'featured_image'
            ); ?>" class="form-control" accept="image/*">

         </div>

         <button type="submit" class="btn btn-primary">
            <?= esc_html(
               $pageData['submit_label']
               ?? ''
            ); ?>
         </button>

      </form>

   <?php endif; ?>

</section>
