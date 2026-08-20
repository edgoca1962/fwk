<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

?>

<section class="container py-5">

   <?php if (have_posts()): ?>

      <?php while (have_posts()): ?>
         <?php the_post(); ?>

         <article>

            <header class="mb-4">

               <h1 class="mb-3">
                  <?= esc_html(
                     get_the_title()
                  ); ?>
               </h1>

               <div class="text-body-secondary">

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

            </header>

            <?php if (has_post_thumbnail()): ?>

               <div class="mb-5">

                  <?php
                  the_post_thumbnail(
                     'large',
                     [
                        'class' =>
                           'img-fluid w-100 rounded',
                     ]
                  );
                  ?>

               </div>

            <?php endif; ?>

            <div class="post-content mb-5">

               <?php the_content(); ?>

            </div>

            <?php
            $categories =
               get_the_category();
            ?>

            <?php if ($categories !== []): ?>

               <div class="mb-3">

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
                        '<a href="%s">%s</a>',
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

               <div class="mb-4">
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

            <nav class="
                  d-flex
                  justify-content-between
                  gap-3
                  border-top
                  pt-4
               ">

               <div>
                  <?php
                  previous_post_link(
                     '%link',
                     '← %title'
                  );
                  ?>
               </div>

               <div class="text-end">
                  <?php
                  next_post_link(
                     '%link',
                     '%title →'
                  );
                  ?>
               </div>

            </nav>

         </article>

      <?php endwhile; ?>

   <?php endif; ?>

</section>
