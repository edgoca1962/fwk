<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$integrations =
   $args['integrations']
   ?? [];

$items =
   $integrations['items']
   ?? [];

?>

<section id="integraciones" class="py-5">

   <div class="container">

      <p class="fs-2 mb-5">
         <?= esc_html(
            $integrations['title']
            ?? ''
         ); ?>
      </p>

   </div>

   <?php if ($items !== []): ?>

      <div class="overflow-hidden">

         <div class="
               scroll-container
               d-flex
            ">
            <div class="
                     scroll-content
                  ">

               <?php for (
                  $copy = 0;
                  $copy < 2;
                  $copy++
               ): ?>


                  <?php foreach (
                     $items as $item
                  ): ?>
                     <div class="item">
                        <a href="<?= esc_url(
                           $item['url']
                           ?? '#'
                        ); ?>" class="
                           text-reset
                           flex-shrink-0
                        " target="_blank" rel="noopener">

                           <img src="<?= esc_url(
                              $item['image_url']
                              ?? ''
                           ); ?>" alt="<?= esc_attr(
                               $item['name']
                               ?? ''
                            ); ?>" class="
                              img-fluid
                              d-block
                           ">

                        </a>
                     </div>
                  <?php endforeach; ?>


               <?php endfor; ?>

            </div>


         </div>

      </div>

   <?php endif; ?>

</section>
