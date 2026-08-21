<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$imageUrl =
   get_template_directory_uri()
   . '/assets/img/core/fgh-consulting-header.png';

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
                  $imageUrl
               ); ?>" class="
                     img-fluid
                     w-100
                  " alt="<?= esc_attr__(
                     'FGH Consulting',
                     'FWK'
                  ); ?>">

            </div>

         </div>

         <div class="col-xl-4">

            <div class="landing-hero__content">

               <h1 class="fs-2 mb-3">

                  <?= esc_html__(
                     'Gobernanza. Eficiencia.',
                     'FWK'
                  ); ?>

                  <br>

                  <span class="text-primary">
                     <?= esc_html__(
                        'Crecimiento',
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
                     'En un entorno donde el volumen de datos y la velocidad del negocio superan con creces las capacidades de los sistemas tradicionales, la gobernanza financiera deja de ser una opción de cumplimiento para convertirse en un imperativo de supervivencia.',
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
                     'Contactar',
                     'FWK'
                  ); ?>
               </a>

            </div>

         </div>

      </div>

   </div>

</section>
