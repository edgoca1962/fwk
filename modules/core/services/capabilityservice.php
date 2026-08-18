<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Administra las capabilities base
 * del Framework.
 */
final class CapabilityService
{
   /**
    * Registra las capabilities base del Framework.
    */
   public function register_base_capabilities(): void
   {
      $administrator =
         get_role('administrator');

      if (!$administrator instanceof \WP_Role) {
         return;
      }

      $administrator->add_cap(
         'fwk_manage_users'
      );
   }
   /**
    * Registra los roles base del Framework.
    */
   public function register_base_roles(): void
   {
      add_role(
         'fwk_general_admin',
         __(
            'Administrador General FRW',
            'FWK'
         ),
         [
            'read' => true,
            'fwk_manage_users' => true,
         ]
      );
   }
}