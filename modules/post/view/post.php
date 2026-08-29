<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$actions =
   is_array($args['actions'] ?? null)
   ? $args['actions']
   : [];

$canEdit =
   (bool) (
      $actions['edit']
      ?? false
   );

$canDelete =
   (bool) (
      $actions['delete']
      ?? false
   );

?>

<div class="col">

   <article class="card h-100 border-0 shadow-sm b_color">

      <?php if (has_post_thumbnail()): ?>

         <a href="<?= esc_url(
            get_permalink()
         ); ?>" class="text-decoration-none">
            <?php
            the_post_thumbnail(
               'medium_large',
               [
                  'class' =>
                     'card-img-top object-fit-cover',
                  'style' =>
                     'height: 220px;',
               ]
            );
            ?>
         </a>

      <?php endif; ?>

      <div class="card-body d-flex flex-column">

         <h2 class="h5 card-title fw-bold">

            <a href="<?= esc_url(
               get_permalink()
            ); ?>" class="text-decoration-none">
               <?= esc_html(
                  get_the_title()
               ); ?>
            </a>

         </h2>

         <div class="small text-body-secondary mb-3">

            <span>
               <?= esc_html(
                  get_the_date()
               ); ?>
            </span>

            <?php if (get_the_author() !== ''): ?>

               <span>
                  ·
                  <?= esc_html(
                     get_the_author()
                  ); ?>
               </span>

            <?php endif; ?>

         </div>

         <?php if (has_excerpt()): ?>

            <div class="card-text mb-3">
               <?= esc_html(
                  get_the_excerpt()
               ); ?>
            </div>

         <?php else: ?>

            <div class="card-text mb-3">
               <?= esc_html(
                  wp_trim_words(
                     wp_strip_all_tags(
                        get_the_content()
                     ),
                     30
                  )
               ); ?>
            </div>

         <?php endif; ?>

         <?php
         $categories =
            get_the_category();
         ?>

         <?php if ($categories !== []): ?>

            <div class="small mb-2">

               <span class="fw-semibold">
                  <?= esc_html__(
                     'Categorías:',
                     'FWK'
                  ); ?>
               </span>

               <?php
               $categoryLinks = [];

               foreach ($categories as $category) {
                  $categoryLinks[] = sprintf(
                     '<a href="%s" class="text-decoration-none">%s</a>',
                     esc_url(
                        get_category_link(
                           $category->term_id
                        )
                     ),
                     esc_html(
                        $category->name
                     )
                  );
               }

               echo implode(
                  ', ',
                  $categoryLinks
               );
               ?>

            </div>

         <?php endif; ?>

         <?php if (has_tag()): ?>

            <div class="small mb-3">
               <?= wp_kses_post(
                  get_the_tag_list(
                     esc_html__(
                        'Etiquetas: ',
                        'FWK'
                     ),
                     ', '
                  )
               ); ?>
            </div>

         <?php endif; ?>

         <div class="mt-auto d-flex gap-2 flex-wrap">

            <a href="<?= esc_url(
               get_permalink()
            ); ?>" class="btn btn-outline-primary btn-sm">
               <?= esc_html__(
                  'Leer artículo',
                  'FWK'
               ); ?>
            </a>

            <?php if ($canEdit): ?>

               <a href="<?= esc_url(
                  add_query_arg(
                     [
                        'post_id' =>
                           get_the_ID(),
                     ],
                     home_url(
                        '/editar-articulo/'
                     )
                  )
               ); ?>" class="btn btn-outline-secondary btn-sm">
                  <?= esc_html__(
                     'Editar',
                     'FWK'
                  ); ?>
               </a>

            <?php endif; ?>

            <?php if ($canDelete): ?>

               <form method="post" action="<?= esc_url(
                  $actions['delete_form_action']
                  ?? ''
               ); ?>" class="d-inline">

                  <input type="hidden" name="action" value="<?= esc_attr(
                     $actions['delete_action']
                     ?? ''
                  ); ?>">

                  <input type="hidden" name="post_id" value="<?= esc_attr(
                     (string) get_the_ID()
                  ); ?>">

                  <?php
                  wp_nonce_field(
                     $actions[
                        'delete_nonce_action'
                     ],
                     $actions[
                        'delete_nonce_name'
                     ]
                  );
                  ?>

                  <button type="submit" class="btn btn-outline-danger btn-sm">
                     <?= esc_html__(
                        'Eliminar',
                        'FWK'
                     ); ?>
                  </button>

               </form>

            <?php endif; ?>

         </div>

      </div>

   </article>

</div>
