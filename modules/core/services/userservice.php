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
    * Convierte un usuario de WordPress
    * en un registro preparado para presentación.
    *
    * @return array<string, mixed>
    */
   private function map_user(
      \WP_User $user,
      string $status
   ): array {
      return [
         'id' => (int) $user->ID,

         'name' => trim(
            $user->first_name
            . ' '
            . $user->last_name
         ),

         'email' =>
            (string) $user->user_email,

         'status' => match ($status) {
            'active' => __(
               'Activo',
               'FWK'
            ),

            'rejected' => __(
               'Rechazado',
               'FWK'
            ),

            'suspended' => __(
               'Suspendido',
               'FWK'
            ),

            default => __(
               'Pendiente',
               'FWK'
            ),
         },
      ];
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
    * Devuelve los usuarios agrupados
    * por estado de cuenta.
    *
    * @return array{
    *    pending: array<int, array<string, mixed>>,
    *    active: array<int, array<string, mixed>>,
    *    rejected: array<int, array<string, mixed>>,
    *    suspended: array<int, array<string, mixed>>
    * }
    */
   public function get_users_by_status(): array
   {
      $users = get_users([
         'role' => 'subscriber',
         'orderby' => 'registered',
         'order' => 'ASC',
      ]);

      $result = [
         'pending' => [],
         'active' => [],
         'rejected' => [],
         'suspended' => [],
      ];

      foreach ($users as $user) {
         if (!$user instanceof \WP_User) {
            continue;
         }

         $status =
            $this->get_account_status(
               $user->ID
            );

         /*
          * Estado vacío o meta inexistente
          * se interpreta como pendiente.
          */
         if ($status === '') {
            $status = 'pending';
         }

         if (
            !isset(
            $result[$status]
         )
         ) {
            continue;
         }

         $result[$status][] =
            $this->map_user(
               $user,
               $status
            );
      }

      return $result;
   }

   /**
    * Cambia una cuenta pendiente
    * al estado administrativo indicado.
    */
   private function transition_pending_user(
      int $userId,
      string $newStatus
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

      if (
         !in_array(
            $newStatus,
            [
               'active',
               'rejected',
               'suspended',
            ],
            true
         )
      ) {
         return false;
      }

      update_user_meta(
         $userId,
         'fwk_account_status',
         $newStatus
      );

      return true;
   }

   /**
    * Activa una cuenta pendiente.
    */
   public function activate_user(
      int $userId
   ): bool {
      return $this->transition_pending_user(
         $userId,
         'active'
      );
   }

   /**
    * Rechaza una cuenta pendiente.
    */
   public function reject_user(
      int $userId
   ): bool {
      return $this->transition_pending_user(
         $userId,
         'rejected'
      );
   }

   /**
    * Suspende una cuenta pendiente.
    */
   public function suspend_user(
      int $userId
   ): bool {
      return $this->transition_pending_user(
         $userId,
         'suspended'
      );
   }

   /**
    * Devuelve un usuario a estado pendiente
    * para una nueva revisión administrativa.
    */
   public function set_user_pending(
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
         !in_array(
            $status,
            [
               'active',
               'rejected',
               'suspended',
            ],
            true
         )
      ) {
         return false;
      }

      update_user_meta(
         $userId,
         'fwk_account_status',
         'pending'
      );

      return true;
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
            'fwk_manage_users'
         )
      );
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
    * Envía al usuario el enlace para
    * establecer una nueva contraseña.
    */
   public function send_activation_email(
      int $userId,
      string $resetUrl
   ): bool {
      $user = get_user_by(
         'id',
         $userId
      );

      if (
         !$user instanceof \WP_User
         || $resetUrl === ''
      ) {
         return false;
      }

      $subject = __(
         'Su acceso al sistema ha sido aprobado',
         'FWK'
      );

      $message = sprintf(
         __(
            "Hola %s,\n\nSu solicitud de ingreso ha sido aprobada.\n\nPara establecer su contraseña, utilice el siguiente enlace:\n\n%s\n\nSaludos.",
            'FWK'
         ),
         $user->first_name !== ''
         ? $user->first_name
         : $user->display_name,
         $resetUrl
      );

      return wp_mail(
         $user->user_email,
         $subject,
         $message
      );
   }

   /**
    * Guarda un mensaje flash para el usuario actual.
    */
   public function set_flash_message(
      string $type,
      string $message,
      array $data = []
   ): void {
      $userId = get_current_user_id();

      if ($userId <= 0) {
         return;
      }

      set_transient(
         'fwk_user_flash_' . $userId,
         [
            'type' => $type,
            'message' => $message,
            'data' => $data,
         ],
         60
      );
   }

   /**
    * Recupera y elimina el mensaje flash.
    *
    * @return array{
    *    type: string,
    *    message: string,
    *    data: array<string, mixed>
    * }
    */
   public function get_flash_message(): array
   {
      $userId = get_current_user_id();

      if ($userId <= 0) {
         return [
            'type' => '',
            'message' => '',
            'data' => [],
         ];
      }

      $key =
         'fwk_user_flash_' . $userId;

      $flash = get_transient($key);

      delete_transient($key);

      if (!is_array($flash)) {
         return [
            'type' => '',
            'message' => '',
            'data' => [],
         ];
      }

      return [
         'type' => (string) (
            $flash['type'] ?? ''
         ),

         'message' => (string) (
            $flash['message'] ?? ''
         ),

         'data' => is_array(
            $flash['data'] ?? null
         )
            ? $flash['data']
            : [],
      ];
   }

   /**
    * Prepara los mensajes flash para presentación.
    *
    * @param array{
    *    type: string,
    *    message: string,
    *    data: array<string, mixed>
    * } $flash
    *
    * @return array<int, array{
    *    class: string,
    *    message: string,
    *    url: string
    * }>
    */
   private function prepare_flash_messages(
      array $flash
   ): array {
      $messages = [];

      if ($flash['message'] !== '') {
         $messages[] = [
            'class' =>
               $flash['type'] === 'success'
               ? 'alert-success'
               : 'alert-danger',

            'message' =>
               $flash['message'],

            'url' => '',
         ];
      }

      if (
         $flash['type'] === 'success'
         && isset(
         $flash['data']['email_sent']
      )
      ) {
         $messages[] = [
            'class' =>
               $flash['data']['email_sent']
               ? 'alert-success'
               : 'alert-warning',

            'message' =>
               $flash['data']['email_sent']
               ? __(
                  'El correo para establecer la contraseña fue enviado correctamente.',
                  'FWK'
               )
               : __(
                  'El usuario fue activado correctamente, pero no fue posible enviar el correo electrónico.',
                  'FWK'
               ),

            'url' => '',
         ];
      }

      if (
         $flash['type'] === 'success'
         && !empty(
         $flash['data']['reset_url']
      )
      ) {
         $messages[] = [
            'class' => 'alert-warning',

            'message' => __(
               'Enlace temporal para establecer contraseña:',
               'FWK'
            ),

            'url' => (string) 
               $flash['data']['reset_url'],
         ];
      }

      return $messages;
   }

   /**
    * Guarda un mensaje flash y redirige
    * a la administración de usuarios.
    *
    * @param array<string, mixed> $data
    */
   private function redirect_with_flash(
      string $type,
      string $message,
      array $data = []
   ): never {
      $this->set_flash_message(
         $type,
         $message,
         $data
      );

      wp_safe_redirect(
         home_url('/usuarios')
      );

      exit;
   }

   /**
    * Valida una acción administrativa POST
    * y devuelve el ID del usuario involucrado.
    *
    * @return int|null
    */
   private function get_action_user_id(
      string $nonceField,
      string $nonceAction
   ): ?int {
      if (
         ($_SERVER['REQUEST_METHOD'] ?? '')
         !== 'POST'
      ) {
         return null;
      }

      if (
         !isset(
         $_POST[$nonceField],
         $_POST['user_id']
      )
      ) {
         return null;
      }

      if (!$this->can_manage_users()) {
         $this->redirect_with_flash(
            'error',
            __(
               'No tiene permisos para realizar esta acción.',
               'FWK'
            )
         );
      }

      $nonce = sanitize_text_field(
         wp_unslash(
            $_POST[$nonceField]
         )
      );

      if (
         !wp_verify_nonce(
            $nonce,
            $nonceAction
         )
      ) {
         $this->redirect_with_flash(
            'error',
            __(
               'No fue posible validar la solicitud.',
               'FWK'
            )
         );
      }

      return absint(
         $_POST['user_id']
      );
   }

   /**
    * Procesa una transición administrativa simple
    * y redirige con el resultado correspondiente.
    *
    * @param callable(int): bool $transition
    */
   private function handle_status_action(
      string $nonceField,
      string $nonceAction,
      callable $transition,
      string $errorMessage,
      string $successMessage
   ): void {
      $userId =
         $this->get_action_user_id(
            $nonceField,
            $nonceAction
         );

      if ($userId === null) {
         return;
      }

      if (!$transition($userId)) {
         $this->redirect_with_flash(
            'error',
            $errorMessage
         );
      }

      $this->redirect_with_flash(
         'success',
         $successMessage
      );
   }

   /**
    * Procesa la aprobación de un usuario pendiente.
    */
   public function handle_activation(): void
   {
      $userId =
         $this->get_action_user_id(
            'fwk_activate_user_nonce',
            'fwk_activate_user'
         );

      if ($userId === null) {
         return;
      }

      if (!$this->activate_user($userId)) {
         $this->redirect_with_flash(
            'error',
            __(
               'No fue posible activar el usuario.',
               'FWK'
            )
         );
      }

      $resetUrl =
         $this->get_password_reset_url(
            $userId
         );

      $emailSent =
         $this->send_activation_email(
            $userId,
            $resetUrl
         );

      $this->redirect_with_flash(
         'success',
         __(
            'El usuario fue activado correctamente.',
            'FWK'
         ),
         [
            'reset_url' =>
               $resetUrl,

            'email_sent' =>
               $emailSent,
         ]
      );
   }

   /**
    * Procesa el rechazo de un usuario pendiente.
    */
   public function handle_rejection(): void
   {
      $this->handle_status_action(
         'fwk_reject_user_nonce',
         'fwk_reject_user',
         fn(int $userId): bool =>
         $this->reject_user($userId),
         __(
            'No fue posible rechazar el usuario.',
            'FWK'
         ),
         __(
            'La solicitud fue rechazada correctamente.',
            'FWK'
         )
      );
   }

   /**
    * Procesa la suspensión de un usuario pendiente.
    */
   public function handle_suspension(): void
   {
      $this->handle_status_action(
         'fwk_suspend_user_nonce',
         'fwk_suspend_user',
         fn(int $userId): bool =>
         $this->suspend_user($userId),
         __(
            'No fue posible suspender el usuario.',
            'FWK'
         ),
         __(
            'El usuario fue suspendido correctamente.',
            'FWK'
         )
      );
   }

   /**
    * Procesa el envío de un usuario
    * nuevamente a revisión.
    */
   public function handle_set_pending(): void
   {
      $this->handle_status_action(
         'fwk_set_pending_nonce',
         'fwk_set_pending',
         fn(int $userId): bool =>
         $this->set_user_pending($userId),
         __(
            'No fue posible enviar el usuario a revisión.',
            'FWK'
         ),
         __(
            'El usuario fue enviado a revisión correctamente.',
            'FWK'
         )
      );
   }

   /**
    * Procesa las acciones administrativas
    * de la pantalla de usuarios.
    */
   public function handle_management_actions(): void
   {
      $this->handle_activation();
      $this->handle_rejection();
      $this->handle_set_pending();
      $this->handle_suspension();
   }

   /**
    * Prepara y procesa la pantalla de
    * administración de usuarios.
    *
    * @return array{
    *    messages: array<int, array{
    *       class: string,
    *       message: string,
    *       url: string
    *    }>,
    *    users: array{
    *       pending: array<int, array<string, mixed>>,
    *       active: array<int, array<string, mixed>>,
    *       rejected: array<int, array<string, mixed>>,
    *       suspended: array<int, array<string, mixed>>
    *    }
    * }
    */
   public function prepare_management_page(): array
   {
      if (!$this->can_manage_users()) {
         wp_safe_redirect(
            home_url('/')
         );

         exit;
      }

      $flash =
         $this->get_flash_message();

      $messages =
         $this->prepare_flash_messages(
            $flash
         );

      $this->handle_management_actions();

      $users =
         $this->get_users_by_status();

      return [
         'messages' =>
            $messages,

         'users' =>
            $users,
      ];
   }
}