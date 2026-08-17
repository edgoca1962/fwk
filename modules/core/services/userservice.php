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

      if (
         $status !== ''
         && $status !== 'pending'
      ) {
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
    * Devuelve los usuarios pendientes de aprobación
    * como datos preparados para presentación.
    *
    * Incluye:
    * - status = pending
    * - status vacío
    * - usuarios sin fwk_account_status
    *
    * @return array<int, array<string, mixed>>
    */
   public function get_pending_users(): array
   {
      $users = get_users([
         'role' => 'subscriber',

         'meta_query' => [
            'relation' => 'OR',

            [
               'key' => 'fwk_account_status',
               'value' => 'pending',
               'compare' => '=',
            ],

            [
               'key' => 'fwk_account_status',
               'value' => '',
               'compare' => '=',
            ],

            [
               'key' => 'fwk_account_status',
               'compare' => 'NOT EXISTS',
            ],
         ],

         'orderby' => 'registered',
         'order' => 'ASC',
      ]);

      $result = [];

      foreach ($users as $user) {
         if (!$user instanceof \WP_User) {
            continue;
         }

         $status =
            $this->get_account_status(
               $user->ID
            );

         if ($status === '') {
            $status = 'pending';
         }

         $result[] = [
            'id' => (int) $user->ID,

            'name' => trim(
               $user->first_name
               . ' '
               . $user->last_name
            ),

            'email' =>
               (string) $user->user_email,

            'status' =>
               ucfirst($status),
         ];
      }

      return $result;
   }
   /**
    * Indica si el usuario actual puede
    * administrar usuarios del Framework.
    */
   public function can_manage_users(): bool
   {
      return (
         is_user_logged_in()
         && current_user_can(
            'manage_options'
         )
      );
   }
   /**
    * Prepara los datos necesarios para la
    * pantalla de administración de usuarios.
    *
    * @return array{
    *    pending_users: array<int, array<string, mixed>>
    * }
    */
   public function get_management_data(): array
   {
      return [
         'pending_users' =>
            $this->get_pending_users(),
      ];
   }
   /**
    * Genera la URL para que el usuario
    * establezca una nueva contraseña.
    */
   public function get_password_reset_url(
      int $userId
   ): string {
      $user = get_user_by(
         'id',
         $userId
      );

      if (!$user instanceof \WP_User) {
         return '';
      }

      $key = get_password_reset_key(
         $user
      );

      if (is_wp_error($key)) {
         return '';
      }

      return network_site_url(
         'wp-login.php?action=rp'
         . '&key=' . rawurlencode($key)
         . '&login=' . rawurlencode($user->user_login),
         'login'
      );
   }

   /**
    * Procesa la aprobación de un usuario pendiente.
    *
    * @return array{
    *    success: bool,
    *    message: string
    * }
    */
   public function handle_activation(): array
   {
      $result = [
         'success' => false,
         'message' => '',
         'reset_url' => '',
      ];

      if (
         ($_SERVER['REQUEST_METHOD'] ?? '')
         !== 'POST'
      ) {
         return $result;
      }

      if (
         !isset(
         $_POST['fwk_activate_user_nonce'],
         $_POST['user_id']
      )
      ) {
         return $result;
      }

      if (!$this->can_manage_users()) {
         $result['message'] = __(
            'No tiene permisos para realizar esta acción.',
            'FWK'
         );

         return $result;
      }

      $nonce = sanitize_text_field(
         wp_unslash(
            $_POST['fwk_activate_user_nonce']
         )
      );

      if (
         !wp_verify_nonce(
            $nonce,
            'fwk_activate_user'
         )
      ) {
         $result['message'] = __(
            'No fue posible validar la solicitud.',
            'FWK'
         );

         return $result;
      }

      $userId = absint(
         $_POST['user_id']
      );

      if (!$this->activate_user($userId)) {
         $result['message'] = __(
            'No fue posible activar el usuario.',
            'FWK'
         );

         return $result;
      }

      $resetUrl =
         $this->get_password_reset_url(
            $userId
         );

      $result['success'] = true;

      $result['message'] = __(
         'El usuario fue activado correctamente.',
         'FWK'
      );

      $result['reset_url'] =
         $resetUrl;

      return $result;
   }
}