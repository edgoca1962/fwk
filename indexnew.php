<?php

if (!defined('ABSPATH')) {
   exit;
}

$core = \FWK\Modules\Core\Core::get_instance();

$view = $core->resolve_view();

get_header();

/*
 * Navbar
 */
if ($view->string('t_navbar') !== '') {
   get_template_part(
      $view->string('t_navbar')
   );
}

/*
 * Banner
 */
if ($view->string('t_banner') !== '') {
   get_template_part(
      $view->string('t_banner')
   );
}

/*
 * Main
 */
?>
<main class="<?= esc_attr(
   $view->string('main')
); ?>">

   <?php

   if ($view->string('t_main') !== '') {
      get_template_part(
         $view->string('t_main')
      );
   }

   ?>

</main>

<?php

/*
 * Footer
 */
if ($view->string('t_footer') !== '') {
   get_template_part(
      $view->string('t_footer')
   );
}

get_footer();