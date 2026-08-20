<?php

declare(strict_types=1);

use FWK\Modules\Core\Core;

if (!defined('ABSPATH')) {
   exit;
}

$view =
   Core::get_instance()
      ->resolve_view();

?>

<!DOCTYPE html>

<html <?php language_attributes(); ?> data-bs-theme="<?= esc_attr(
      $view->string(
         'html',
         'dark'
      )
   ); ?>">

<head>

   <meta charset="<?php bloginfo('charset'); ?>">

   <meta name="viewport" content="width=device-width, initial-scale=1">

   <?php wp_head(); ?>

</head>

<body class="<?= esc_attr(
   $view->css('body')
); ?>">

   <?php wp_body_open(); ?>

   <?php
   $view->render(
      't_navbar'
   );
   ?>

   <main>

      <?php
      if (
         $view->string(
            't_main'
         ) !== ''
      ) {
         $view->render(
            't_main'
         );
      } else {
         $view->render(
            't_none'
         );
      }
      ?>

   </main>

   <?php wp_footer(); ?>

</body>

</html>
