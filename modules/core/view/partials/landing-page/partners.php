<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$partners =
   $args['partners']
   ?? [];

$sectionClasses =
   $partners['section_classes']
   ?? [];

$items =
   $partners['items']
   ?? [];

?>

<section id="partners"
   class="<?= esc_attr($sectionClasses['background'] ?? ''); ?> <?= esc_attr($sectionClasses['text'] ?? ''); ?> py-5">

   <div class="container">

      <p class="fs-2 mb-4 text-dark">
         <?= esc_html__(
            $partners['title'],
            'FWK'
         ); ?>
      </p>

   </div>

   <div class="scroll-container">

      <div class="scroll-content">

         <?php for ($copy = 0; $copy < 4; $copy++): ?>

            <?php foreach ($items as $partner): ?>

               <div class="item">

                  <a href="<?= esc_url(
                     $partner['url']
                     ?? '#'
                  ); ?>" class="text-reset" target="_blank" rel="noopener">

                     <img src="<?= esc_url(
                        $partner['image_url']
                        ?? ''
                     ); ?>" alt="<?= esc_attr(
                         $partner['name']
                         ?? ''
                      ); ?>" class="
                           img-fluid
                        ">

                  </a>

               </div>

            <?php endforeach; ?>

         <?php endfor; ?>

      </div>

   </div>

</section>
