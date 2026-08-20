<?php

declare(strict_types=1);

use FWK\Modules\Core\Services\NavigationService;

if (!defined('ABSPATH')) {
   exit;
}

$navigation =
   new NavigationService();

?>

<section class="
      d-flex
      flex-column
      justify-content-center
      align-items-center
      text-center
      min-vh-100
      px-3
   ">

   <h1 class="display-6 mb-3">
      <?= esc_html__(
         'Página no encontrada',
         'FWK'
      ); ?>
   </h1>

   <p class="mb-4">
      <?= esc_html__(
         'La página que buscas no existe o ya no está disponible.',
         'FWK'
      ); ?>
   </p>

   <a href="<?= esc_url(
      $navigation->get_home_url()
   ); ?>" class="btn btn-primary">
      <?= esc_html__(
         'Volver al inicio',
         'FWK'
      ); ?>
   </a>

</section>
