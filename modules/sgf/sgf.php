<?php

declare(strict_types=1);

namespace FWK\Modules\SGF;

use FWK\Modules\Core\AbstractModule;
use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Módulo de finanzas personales.
 *
 * @package FWK
 */
final class SGF extends AbstractModule
{
   use Singleton;

   protected function __construct()
   {
   }

   protected function register(): void
   {
      /*
       * Más adelante:
       *
       * SGFPostTypes::get_instance();
       * SGFTaxonomies::get_instance();
       * SGFRoles::get_instance();
       * SGFAjax::get_instance();
       */
   }
}