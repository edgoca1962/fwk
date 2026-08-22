<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$strategy =
   $args['strategy']
   ?? [];

$sectionClasses =
   $strategy['section_classes']
   ?? [];

$quote =
   $strategy['quote']
   ?? [];

$quoteClasses =
   $quote['section_classes']
   ?? [];

?>

<section id="strategy" class="
      mt-5
      <?= esc_attr(
         $sectionClasses['background']
         ?? ''
      ); ?>
      <?= esc_attr(
         $sectionClasses['text']
         ?? ''
      ); ?>
   ">

   <div class="container">

      <div class="
            row
            align-items-center
            g-5
         ">

         <div class="col-xl-6 py-5">

            <div class="
                  overflow-hidden
                  rounded-5
                  landing-reveal
               ">

               <img src="<?= esc_url(
                  $strategy['image_url']
                  ?? ''
               ); ?>" alt="<?= esc_attr(
                   $strategy['image_alt']
                   ?? ''
                ); ?>" class="
                     img-fluid
                     w-100
                  ">

            </div>

         </div>

         <div class="col-xl-6 py-5">

            <div class="landing-reveal">

               <h2 class="fs-2 mb-3">
                  <?= esc_html(
                     $strategy['title']
                     ?? ''
                  ); ?>
               </h2>

               <p class="
                     font-second
                     fs-5
                     mb-4
                  ">
                  <?= esc_html(
                     $strategy['text']
                     ?? ''
                  ); ?>
               </p>

               <?php foreach (
                  $strategy['items']
                  ?? []
                  as $item
               ): ?>

                  <div class="
                        d-flex
                        align-items-center
                        mb-3
                        font-second
                     ">

                     <span class="fs-3 me-3">

                        <i class="
                              bi
                              <?= esc_attr(
                                 $item['icon']
                                 ?? ''
                              ); ?>
                           "></i>

                     </span>

                     <span class="fs-5">
                        <?= esc_html(
                           $item['text']
                           ?? ''
                        ); ?>
                     </span>

                  </div>

               <?php endforeach; ?>

               <a href="<?= esc_url(
                  $strategy['button_url']
                  ?? '#'
               ); ?>" class="
                     btn
                     btn-secondary
                     text-dark
                     bg-transparent
                     rounded-pill
                     px-4
                     py-2
                     mt-3
                  ">
                  <?= esc_html(
                     $strategy['button_text']
                     ?? ''
                  ); ?>
               </a>

            </div>

         </div>

      </div>

   </div>

</section>

<section class="
      <?= esc_attr(
         $quoteClasses['background']
         ?? ''
      ); ?>
      <?= esc_attr(
         $quoteClasses['text']
         ?? ''
      ); ?>
   ">

   <div class="container py-5 landing-reveal">

      <figure class="mb-0">

         <blockquote class="blockquote">

            <p class="display-5">
               <?= esc_html(
                  $quote['text']
                  ?? ''
               ); ?>
            </p>

         </blockquote>

         <?php if (
            ($quote['author'] ?? '')
            !== ''
         ): ?>

            <figcaption class="blockquote-footer">

               <cite>
                  <?= esc_html(
                     $quote['author']
                  ); ?>
               </cite>

            </figcaption>

         <?php endif; ?>

      </figure>

   </div>

</section>
