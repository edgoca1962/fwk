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
/******************************************************************************
 * 
 * Àrea de pruebas
 * 
 *****************************************************************************/
use FWK\Modules\Core\Services\FilterConfigService;
use FWK\Modules\Core\Services\FilterRequestService;
use FWK\Modules\Core\Services\FilterQueryService;

$filterConfigService =
   new FilterConfigService();

$filterRequestService =
   new FilterRequestService();

$filterConfig =
   $filterConfigService->load_for_post_type(
      'post'
   );

$filters =
   $filterRequestService->resolve(
      $filterConfig
   );

$filterQueryService =
   new FilterQueryService();

$queryArgs =
   $filterQueryService->build(
      'post',
      $filters
   );

echo '<pre>';
print_r(
   $queryArgs
);
echo '</pre>';


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
         the_posts_pagination([
            'prev_text' => '&laquo; Anterior',
            'mid_size' => 1,
            'next_text' => 'Siguiente &raquo;',
         ]);
         ?>

      </div>

   <?php endif; ?>

</section>
