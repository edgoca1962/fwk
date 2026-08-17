<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Servicio para solicitudes de ingreso de usuarios.
 */
final class UserRequestService
{
   /**
    * Indica si el formulario fue enviado.
    */
   public function is_submitted(): bool
   {
      return (
         ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
         && isset(
         $_POST['fwk_request_access_nonce']
      )
      );
   }

   /**
    * Valida el nonce del formulario.
    */
   public function is_nonce_valid(): bool
   {
      if (!$this->is_submitted()) {
         return false;
      }

      $nonce = sanitize_text_field(
         wp_unslash(
            $_POST['fwk_request_access_nonce']
         )
      );

      return wp_verify_nonce(
         $nonce,
         'fwk_request_access_action'
      ) !== false;
   }

   /**
    * Devuelve el nombre ingresado.
    */
   public function get_first_name(): string
   {
      if (!isset($_POST['first_name'])) {
         return '';
      }

      return sanitize_text_field(
         wp_unslash(
            $_POST['first_name']
         )
      );
   }

   /**
    * Devuelve los apellidos ingresados.
    */
   public function get_last_name(): string
   {
      if (!isset($_POST['last_name'])) {
         return '';
      }

      return sanitize_text_field(
         wp_unslash(
            $_POST['last_name']
         )
      );
   }

   /**
    * Devuelve el correo ingresado.
    */
   public function get_email(): string
   {
      if (!isset($_POST['user_email'])) {
         return '';
      }

      return sanitize_email(
         wp_unslash(
            $_POST['user_email']
         )
      );
   }
   /**
    * Valida los datos enviados.
    *
    * @return string[]
    */
   public function validate(): array
   {
      $errors = [];

      if (!$this->is_submitted()) {
         return $errors;
      }

      if (!$this->is_nonce_valid()) {
         $errors[] = __(
            'No fue posible validar la solicitud. Recargue la página e inténtelo nuevamente.',
            'FWK'
         );

         return $errors;
      }

      $firstName = $this->get_first_name();
      $lastName = $this->get_last_name();
      $email = $this->get_email();

      if ($firstName === '') {
         $errors[] = __(
            'Debe ingresar su nombre.',
            'FWK'
         );
      }

      if ($lastName === '') {
         $errors[] = __(
            'Debe ingresar sus apellidos.',
            'FWK'
         );
      }

      if (
         $email === ''
         || !is_email($email)
      ) {
         $errors[] = __(
            'Debe ingresar un correo electrónico válido.',
            'FWK'
         );
      }

      if (
         $email !== ''
         && email_exists($email)
      ) {
         $errors[] = __(
            'Ya existe un usuario registrado con ese correo electrónico.',
            'FWK'
         );
      }

      return $errors;
   }
   /**
    * Crea un usuario pendiente a partir de la solicitud.
    *
    * @return int|\WP_Error
    */
   public function create_pending_user(): int|\WP_Error
   {
      $errors = $this->validate();

      if ($errors !== []) {
         return new \WP_Error(
            'fwk_invalid_request',
            implode(' ', $errors)
         );
      }

      $email = $this->get_email();

      /*
       * Usaremos el correo como user_login inicial
       * para simplificar el flujo de acceso.
       */
      $userLogin = $email;

      /*
       * El usuario no conocerá esta contraseña.
       * Posteriormente el administrador enviará
       * el enlace para establecer/restablecer clave.
       */
      $password = wp_generate_password(
         32,
         true,
         true
      );

      $userId = wp_insert_user([
         'user_login' => $userLogin,
         'user_email' => $email,
         'user_pass' => $password,
         'first_name' => $this->get_first_name(),
         'last_name' => $this->get_last_name(),
         'role' => 'subscriber',
      ]);

      if (is_wp_error($userId)) {
         return $userId;
      }

      update_user_meta(
         $userId,
         'fwk_account_status',
         'pending'
      );
      update_user_meta(
         $userId,
         'show_admin_bar_front',
         'false'
      );
      return $userId;
   }
   /**
    * Procesa completamente una solicitud de ingreso.
    *
    * @return array{
    *    submitted: bool,
    *    success: bool,
    *    errors: string[],
    *    user_id: int
    * }
    */
   public function handle_request(): array
   {
      $result = [
         'submitted' => false,
         'success' => false,
         'errors' => [],
         'user_id' => 0,

         'values' => [
            'first_name' => $this->get_first_name(),
            'last_name' => $this->get_last_name(),
            'user_email' => $this->get_email(),
         ],
      ];

      if (!$this->is_submitted()) {
         return $result;
      }

      $result['submitted'] = true;

      $errors = $this->validate();

      if ($errors !== []) {
         $result['errors'] = $errors;

         return $result;
      }

      $userId = $this->create_pending_user();

      if (is_wp_error($userId)) {
         $result['errors'][] =
            $userId->get_error_message();

         return $result;
      }

      $result['success'] = true;
      $result['user_id'] = $userId;

      return $result;
   }
}