<?php

declare(strict_types=1);

use FWK\Modules\Core\Services\LandingPageService;

if (!defined('ABSPATH')) {
      exit;
}

$landingPageService =
      new LandingPageService();

$pageData =
      $landingPageService->prepare_page();

?>

<div class="landing-page">

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/hero',
            null,
            [
                  'hero' =>
                        $pageData['hero'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/partners',
            null,
            [
                  'partners' =>
                        $pageData['partners'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/services',
            null,
            [
                  'services' =>
                        $pageData['services'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/strategy',
            null,
            [
                  'strategy' =>
                        $pageData['strategy'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/integrations',
            null,
            [
                  'integrations' =>
                        $pageData['integrations'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/governance',
            null,
            [
                  'governance' =>
                        $pageData['governance'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/coaching',
            null,
            [
                  'coaching' =>
                        $pageData['coaching'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/experience',
            null,
            [
                  'experience' =>
                        $pageData['experience'],
            ]
      );
      ?>

      <?php
      get_template_part(
            'modules/core/view/partials/landing-page/contact',
            null,
            [
                  'contact' =>
                        $pageData['contact'],
            ]
      );
      ?>

</div>
