<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Servicio de autenticación del Framework.
 */
final class AuthService
{
   /**
    * Indica si el formulario de login fue enviado.
    */
   public function is_login_submitted(): bool
   {
      return (
         ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
         && isset($_POST['fwk_login_nonce'])
      );
   }
   /**
    * Valida el nonce del formulario de login.
    */
   public function is_login_nonce_valid(): bool
   {
      if (!$this->is_login_submitted()) {
         return false;
      }

      $nonce = sanitize_text_field(
         wp_unslash(
            $_POST['fwk_login_nonce']
         )
      );

      return wp_verify_nonce(
         $nonce,
         'fwk_login_action'
      ) !== false;
   }
   /**
    * Intenta autenticar al usuario.
    *
    * @return \WP_User|\WP_Error
    */
   public function login(): \WP_User|\WP_Error
   {
      $credentials = [
         'user_login' => sanitize_text_field(
            wp_unslash(
               $_POST['user_login'] ?? ''
            )
         ),

         'user_password' => (string) (
            $_POST['user_password']
            ?? ''
         ),

         'remember' => isset(
            $_POST['remember']
         ),
      ];

      return wp_signon(
         $credentials,
         is_ssl()
      );
   }
   /**
    * Indica si existe un usuario autenticado.
    */
   public function is_authenticated(): bool
   {
      return is_user_logged_in();
   }
   /**
    * Devuelve el usuario ingresado en el formulario.
    */
   public function get_submitted_user(): string
   {
      if (!isset($_POST['user_login'])) {
         return '';
      }

      return sanitize_text_field(
         wp_unslash(
            $_POST['user_login']
         )
      );
   }
   /**
    * Indica si el usuario marcó "Recordarme".
    */
   public function is_remember_requested(): bool
   {
      return isset(
         $_POST['remember']
      );
   }
   /**
    * Determina si el usuario tiene permitido ingresar.
    */
   public function can_user_login(
      \WP_User $user
   ): bool {
      /*
       * El administrador nativo de WordPress
       * siempre tiene acceso.
       */
      if (
         user_can(
            $user,
            'manage_options'
         )
      ) {
         return true;
      }

      $status = (string) get_user_meta(
         $user->ID,
         'fwk_account_status',
         true
      );

      return $status === 'active';
   }
   /**
    * Procesa completamente el intento de login.
    *
    * Devuelve:
    * - string vacío si el login fue exitoso.
    * - mensaje de error si falló.
    */
   public function handle_login(): string
   {
      if (!$this->is_login_submitted()) {
         return '';
      }

      if (!$this->is_login_nonce_valid()) {
         return __(
            'No fue posible validar la solicitud. Por favor, recargue la página e inténtelo nuevamente.',
            'FWK'
         );
      }

      $user = $this->login();

      if (is_wp_error($user)) {
         return __(
            'El usuario o la contraseña ingresados no son correctos.',
            'FWK'
         );
      }
      if (!$this->can_user_login($user)) {

         wp_logout();

         return __(
            'Su cuenta todavía no se encuentra habilitada para ingresar al sistema.',
            'FWK'
         );
      }
      wp_safe_redirect(
         home_url('/')
      );

      exit;
   }
}