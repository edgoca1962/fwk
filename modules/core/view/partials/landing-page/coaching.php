<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$coaching =
   $args['coaching']
   ?? [];

$sectionClasses =
   $coaching['section_classes']
   ?? [];

$quote =
   $coaching['quote']
   ?? [];

$quoteClasses =
   $quote['section_classes']
   ?? [];

?>

<section id="coaching" class="
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

   <div class="container autoShow">

      <div class="
            row
            align-items-center
            g-5
         ">

         <div class="col-lg-6 py-5">

            <div class="landing-reveal">

               <h2 class="fs-2 mb-3">
                  <?= esc_html(
                     $coaching['title']
                     ?? ''
                  ); ?>
               </h2>

               <p class="
                     font-second
                     fs-5
                     mb-4
                  ">
                  <?= esc_html(
                     $coaching['text']
                     ?? ''
                  ); ?>
               </p>

               <?php foreach (
                  $coaching['items']
                  ?? []
                  as $item
               ): ?>

                  <div class="
                        d-flex
                        align-items-start
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

                     <div>

                        <span class="fs-5">
                           <?= esc_html(
                              $item['text']
                              ?? ''
                           ); ?>
                        </span>

                        <?php if (
                           !empty(
                           $item['links']
                        )
                           && is_array(
                              $item['links']
                           )
                        ): ?>

                           <div class="small mt-1">

                              (

                              <?php foreach (
                                 $item['links']
                                 as $index => $link
                              ): ?>

                                 <?php if ($index > 0): ?>
                                    <?= esc_html__(
                                       ' y ',
                                       'FWK'
                                    ); ?>
                                 <?php endif; ?>

                                 <a href="<?= esc_url(
                                    $link['url']
                                    ?? '#'
                                 ); ?>" class="text-reset" target="_blank" rel="noopener">
                                    <?= esc_html(
                                       $link['label']
                                       ?? ''
                                    ); ?>
                                 </a>

                              <?php endforeach; ?>

                              )

                           </div>

                        <?php endif; ?>

                     </div>

                  </div>

               <?php endforeach; ?>

               <a href="<?= esc_url(
                  $coaching['button_url']
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
                     $coaching['button_text']
                     ?? ''
                  ); ?>
               </a>

            </div>

         </div>

         <div class="
               col-lg-6
               py-5
               d-flex
               justify-content-center
            ">

            <div class="
                  overflow-hidden
                  rounded-5
                  landing-reveal
               " style="max-width: 24rem;">

               <img src="<?= esc_url(
                  $coaching['image_url']
                  ?? ''
               ); ?>" alt="<?= esc_attr(
                   $coaching['image_alt']
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

   <div class="container py-5 autoShow">

      <p class="display-5 mb-0">
         <?= esc_html(
            $quote['text']
            ?? ''
         ); ?>
      </p>

   </div>

</section>
