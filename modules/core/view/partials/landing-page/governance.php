<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$governance =
   $args['governance']
   ?? [];

$sectionClasses =
   $governance['section_classes']
   ?? [];

$quote =
   $governance['quote']
   ?? [];

$quoteClasses =
   $quote['section_classes']
   ?? [];

?>

<section id="gobernanza" class="
      <?= esc_attr(
         $sectionClasses['background']
         ?? ''
      ); ?>
      <?= esc_attr(
         $sectionClasses['text']
         ?? ''
      ); ?>
   ">

   <div class="container py-5 autoShow">

      <div class="
            row
            align-items-center
            g-5
         ">

         <div class="col-xl-6">

            <h2 class="fs-2 mb-3">
               <?= esc_html(
                  $governance['title']
                  ?? ''
               ); ?>
            </h2>

            <p class="
                  font-second
                  fs-5
                  mb-4
               ">
               <?= esc_html(
                  $governance['text']
                  ?? ''
               ); ?>
            </p>

            <?php foreach (
               $governance['items']
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
               $governance['button_url']
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
                  $governance['button_text']
                  ?? ''
               ); ?>
            </a>

         </div>

         <div class="col-xl-6">

            <div class="
                  overflow-hidden
                  rounded-5
               ">

               <img src="<?= esc_url(
                  $governance['image_url']
                  ?? ''
               ); ?>" alt="<?= esc_attr(
                   $governance['image_alt']
                   ?? ''
                ); ?>" class="
                     img-fluid
                     w-100
                  ">

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

      <p class="display-5 mb-0">
         <?= esc_html(
            $quote['text']
            ?? ''
         ); ?>
      </p>

   </div>

</section>
