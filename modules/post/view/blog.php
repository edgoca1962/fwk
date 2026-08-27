<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Post\Services\PostViewService;

$postView =
   new PostViewService();

$pageData =
   $postView->prepare_blog_page();

$filterData =
   $pageData['filters']
   ?? [];

/******************************************************************************
 * 
 * Àrea de pruebas
 * 
 *****************************************************************************/

?>

<section class="container py-5">

   <?php if ($pageData['title'] !== ''): ?>

      <header class="mb-4">

         <h1>
            <?= esc_html(
               $pageData['title']
            ); ?>
         </h1>

      </header>

   <?php endif; ?>

   <?php

   get_template_part(
      'modules/post/view/partials/blog/filters',
      null,
      $filterData,
   );

   ?>

   <div class="
         row
         row-cols-1
         row-cols-md-2
         row-cols-xl-3
         g-4
      ">

      <?php if (have_posts()): ?>

         <?php while (have_posts()): ?>
            <?php the_post(); ?>

            <?php
            get_template_part(
               'modules/post/view/post'
            );
            ?>

         <?php endwhile; ?>

      <?php endif; ?>

   </div>
   <?php if (get_the_posts_pagination()): ?>

      <div class="mt-5">

         <?php

         $addArgs = [];

         foreach ($_GET as $key => $value) {

            if (
               is_scalar($value)
               && $value !== ''
            ) {
               $addArgs[
                  sanitize_key(
                     (string) $key
                  )
               ] =
                  sanitize_text_field(
                     wp_unslash(
                        (string) $value
                     )
                  );
            }

         }

         unset(
            $addArgs['paged']
         );

         the_posts_pagination([
            'prev_text' =>
               '&laquo; Anterior',

            'mid_size' =>
               1,

            'next_text' =>
               'Siguiente &raquo;',

            'add_args' =>
               $addArgs,
         ]);

         ?>

      </div>

   <?php endif; ?>

</section>
