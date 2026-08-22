<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$services =
   $args['services']
   ?? [];

$sectionClasses =
   $services['section_classes']
   ?? [];

$items =
   $services['items']
   ?? [];

?>

<section id="servicios" class="py-5 <?= esc_attr($sectionClasses['background'] ?? ''); ?>
      <?= esc_attr($sectionClasses['text'] ?? ''); ?>">

   <div class="container">
      <?php if ($services !== []): ?>
         <div class="row row-cols-1 row-cols-xl-4 justify-content-between g-3">
            <?php foreach ($items as $index => $service): ?>

               <?php
               $variant = $service['variant'] ?? 'light';
               $textClass = match ($variant) { 'dark' => 'text-light', default => 'text-dark', };
               $buttonClass = match ($variant) { 'dark' => 'btn-light text-light', 'orange' => 'btn-light text-light', default => 'btn-secondary text-dark', };
               ?>

               <div class="col d-flex justify-content-center autoShow">
                  <div class="card rounded-5 border-0 position-relative" style="width: 20rem;">
                     <img src="<?= esc_url($service['image_url'] ?? ''); ?>"
                        alt="<?= esc_attr($service['image_alt'] ?? ''); ?>" class="card-img rounded-5 object-fit-cover">
                     <div
                        class="card-img-overlay d-flex flex-column justify-content-between h-100 p-4 <?= esc_attr($textClass); ?>">
                        <div>
                           <h2 class=" h3 card-title">
                              <?= esc_html($service['title'] ?? ''); ?>
                           </h2>
                        </div>
                        <div>
                           <a href="<?= esc_url($service['url'] ?? '#'); ?>"
                              class="btn <?= esc_attr($buttonClass); ?> bg-transparent rounded-pill px-4py-2">
                              <?= esc_html($service['button_text'] ?? ''); ?>
                           </a>
                        </div>
                     </div>
                  </div>
               </div>

            <?php endforeach; ?>
         </div>

      <?php endif; ?>

   </div>

</section>
