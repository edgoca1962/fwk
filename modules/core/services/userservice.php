<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Servicio para administración de usuarios
 * del Framework.
 */
final class UserService
{
   /**
    * Activa una cuenta pendiente.
    */
   public function activate_user(
      int $userId
   ): bool {
      $user = get_user_by(
         'id',
         $userId
      );

      if (!$user instanceof \WP_User) {
         return false;
      }

      $status =
         $this->get_account_status(
            $userId
         );

      if ($status !== 'pending') {
         return false;
      }

      update_user_meta(
         $userId,
         'fwk_account_status',
         'active'
      );

      return true;
   }
   /**
    * Devuelve el estado de la cuenta del usuario.
    */
   public function get_account_status(
      int $userId
   ): string {
      return (string) get_user_meta(
         $userId,
         'fwk_account_status',
         true
      );
   }
   /**
    * Devuelve los usuarios pendientes de aprobación.
    *
    * @return \WP_User[]
    */
   public function get_pending_users(): array
   {
      $users = get_users([
         'role' => 'subscriber',

         'meta_key' => 'fwk_account_status',
         'meta_value' => 'pending',

         'orderby' => 'registered',
         'order' => 'ASC',
      ]);

      return is_array($users)
         ? $users
         : [];
   }
}