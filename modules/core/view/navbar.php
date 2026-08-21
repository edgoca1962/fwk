<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Core\Support\WalkerNav;
use FWK\Modules\Core\Services\NavigationService;
$navigation =
   new NavigationService();

$customLogoId =
   (int) get_theme_mod(
      'custom_logo'
   );

$logoUrl =
   $customLogoId > 0
   ? wp_get_attachment_image_url(
      $customLogoId,
      'full'
   )
   : false;

?>

<!-- bg-body-tertiary -->
<nav id="site-navbar" class="navbar navbar-expand-lg bg-transparent fixed-top mb-5">
   <div class="container">

      <a class="navbar-brand d-flex align-items-center" href="<?= esc_url(
         $navigation->get_home_url()
      ); ?>">
         <img id='site-logo' src="<?= esc_url($logoUrl ?? "") ?>" alt="<?= esc_attr(get_bloginfo('name')); ?>"
            width="60" height="60" class="d-inline-block align-text-top me-2">
         <?= esc_html(get_bloginfo('name')); ?>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fwk-navbar"
         aria-controls="fwk-navbar" aria-expanded="false" aria-label="<?= esc_attr__(
            'Mostrar navegación',
            'FWK'
         ); ?>">
         <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="fwk-navbar">

         <?php
         wp_nav_menu([
            'theme_location' => 'publico',
            'container' => false,
            'menu_class' =>
               'navbar-nav ms-auto mb-2 mb-lg-0',
            'fallback_cb' => false,
            'walker' => new WalkerNav(),
         ]);
         ?>

      </div>

   </div>
</nav>
<div style="padding-top:72px;"></div>
<script>
   const navbar =
      document.getElementById('site-navbar');

   if (navbar) {

      const updateNavbar = () => {

         navbar.classList.toggle(
            'navbar-scrolled',
            window.scrollY > 50
         );

      };

      updateNavbar();

      window.addEventListener(
         'scroll',
         updateNavbar,
         { passive: true }
      );

   }
</script>
