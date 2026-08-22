<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$partners =
   $args['partners']
   ?? [];

?>

<section id="partners" class="py-5" style="background: #d9d9d9;">

   <div class="container">

      <p class="fs-2 mb-4 text-dark">
         <?= esc_html__(
            $partners['title'],
            'FWK'
         ); ?>
      </p>

   </div>

   <div class="overflow-hidden">

      <div class="
            landing-marquee
            d-flex
         ">

         <?php for ($copy = 0; $copy < 2; $copy++): ?>

            <div class="
                  landing-marquee__group
                  d-flex
                  align-items-center
                  justify-content-around
                  flex-shrink-0
               ">

               <?php foreach ($partners as $partner): ?>

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
                           d-block
                        ">

                  </a>

               <?php endforeach; ?>

            </div>

         <?php endfor; ?>

      </div>

   </div>

</section>
