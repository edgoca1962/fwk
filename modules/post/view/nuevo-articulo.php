<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Post\Services\PostViewService;

$pageData =
   (new PostViewService())
      ->prepare_create_form();

?>

<main class="container py-5">

   <div class="row justify-content-center">
      <div class="col-lg-8">

         <h1 class="mb-4">
            <?= esc_html($pageData['title']); ?>
         </h1>

         <form method="post" action="<?= esc_url($pageData['form_action']); ?>" enctype="multipart/form-data">

            <input type="hidden" name="action" value="<?= esc_attr($pageData['action']); ?>">

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
                  ); ?>
               </label>

               <input type="text" id="post-title" name="<?= esc_attr(
                  $pageData['fields']['title']['name']
               ); ?>" class="form-control" required>
            </div>

            <div class="mb-4">
               <label for="post-content" class="form-label">
                  <?= esc_html(
                     $pageData['fields']['content']['label']
                  ); ?>
               </label>

               <textarea id="post-content" name="<?= esc_attr(
                  $pageData['fields']['content']['name']
               ); ?>" class="form-control" rows="10"></textarea>
            </div>

            <div class="mb-4">
               <label for="post-featured-image" class="form-label">
                  <?= esc_html__(
                     'Imagen destacada',
                     'FWK'
                  ); ?>
               </label>

               <input type="file" id="post-featured-image" name="featured_image" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">
               <?= esc_html(
                  $pageData['submit_label']
               ); ?>
            </button>

         </form>

      </div>
   </div>

</main>
