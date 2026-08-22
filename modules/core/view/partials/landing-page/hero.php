<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$hero =
   $args['hero']
   ?? [];

?>

<section id="hero" class="landing-hero py-5">

   <div class="container">

      <div class="
            row
            align-items-center
            g-5
         ">

         <div class="col-xl-8">

            <div class="
                  landing-hero__image
                  overflow-hidden
                  rounded-5
               ">

               <img src="<?= esc_url(
                  $hero['image_url']
               ); ?>" class="
                     img-fluid
                     w-100
                  " alt="<?= esc_attr__(
                     $hero['image_alt'],
                     'FWK'
                  ); ?>">

            </div>

         </div>

         <div class="col-xl-4">

            <div class="landing-hero__content">

               <h1 class="fs-2 mb-3">

                  <span class="text-primary">
                     <?= esc_html__(
                        $hero['title1'],
                        'FWK'
                     ); ?>
                  </span>

                  <br>

                  <span>
                     <?= esc_html__(
                        $hero['title2'],
                        'FWK'
                     ); ?>
                  </span>

                  <br>

                  <span class="text-primary">
                     <?= esc_html__(
                        $hero['title3'],
                        'FWK'
                     ); ?>
                  </span>

               </h1>

               <p class="
                     font-second
                     fs-5
                     mb-4
                  ">
                  <?= esc_html__(
                     $hero['text'],
                     'FWK'
                  ); ?>
               </p>

               <a href="#contacto" class="
                     btn
                     btn-primary
                     bg-transparent
                     border-1
                     px-4
                     py-2
                     rounded-pill
                  ">
                  <?= esc_html__(
                     $hero['button_text'],
                     'FWK'
                  ); ?>
               </a>

            </div>

         </div>

      </div>

   </div>

</section>
