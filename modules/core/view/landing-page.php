<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
      exit;
}

use FWK\Modules\Core\Services\LandingPageService;

$landingPageService =
      new LandingPageService();

echo '<pre>';
print_r(
      $landingPageService->prepare_page()
);
echo '</pre>';

?>

<div class="landing-page">

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/hero'
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/partners'
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/services'
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/governance'
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/coaching'
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/experience'
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/contact'
      );
      ?>

</div>
