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

         'remember' => true,
      ];

      return wp_signon(
         $credentials,
         is_ssl()
      );
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
      if (
         !$this->is_login_submitted()
         || !$this->is_login_nonce_valid()
      ) {
         return '';
      }

      $user = $this->login();

      if (is_wp_error($user)) {
         return $user->get_error_message();
      }

      wp_safe_redirect(
         home_url('/')
      );

      exit;
   }
}