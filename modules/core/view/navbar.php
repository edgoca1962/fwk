<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Core\Support\WalkerNav;


?>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
   <div class="container">

      <a class="navbar-brand" href="<?= esc_url(
         home_url('/')
      ); ?>">
         <?= esc_html(
            get_bloginfo('name')
         ); ?>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fwk-navbar"
         aria-controls="fwk-navbar" aria-expanded="false" aria-label="<?= esc_attr__(
            'Mostrar navegación',
            'FWK'
         ); ?>">
         <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="fwk-navbar">

         <?php if (is_user_logged_in()): ?>

            <?php
            wp_nav_menu([
               'theme_location' => 'principal',
               'container' => false,
               'menu_class' =>
                  'navbar-nav ms-auto mb-2 mb-lg-0',
               'fallback_cb' => false,
               'walker' => new WalkerNav(),
            ]);
            ?>

         <?php else: ?>

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

         <?php endif; ?>
      </div>
   
   </div>
</nav>
