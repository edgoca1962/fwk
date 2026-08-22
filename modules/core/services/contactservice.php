<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Procesa el formulario de contacto
 * de la Landing Page.
 */
final class ContactService
{
   /**
    * Nombre del nonce.
    */
   private const NONCE_NAME =
      'fwk_contact_nonce';

   /**
    * Acción asociada al nonce.
    */
   private const NONCE_ACTION =
      'fwk_contact_submit';

   /**
    * Determina si la solicitud actual
    * corresponde al formulario de contacto.
    */
   public function is_submission(): bool
   {
      return (
         $_SERVER['REQUEST_METHOD']
         ?? ''
      ) === 'POST'
         && isset(
         $_POST['enviar_mensaje']
      );
   }

   /**
    * Genera el campo nonce
    * utilizado por el formulario.
    */
   public function render_nonce(): void
   {
      wp_nonce_field(
         self::NONCE_ACTION,
         self::NONCE_NAME
      );
   }
   /**
    * Valida el nonce del formulario.
    */
   public function verify_nonce(): bool
   {
      $nonce =
         $_POST[self::NONCE_NAME]
         ?? '';

      if (!is_string($nonce)) {
         return false;
      }

      return wp_verify_nonce(
         sanitize_text_field(
            wp_unslash($nonce)
         ),
         self::NONCE_ACTION
      ) !== false;
   }

   /**
    * Obtiene y sanitiza los datos enviados
    * por el formulario de contacto.
    *
    * @return array{
    *    nombre: string,
    *    whatsapp: string,
    *    email: string,
    *    mensaje: string
    * }
    */
   private function get_data(): array
   {
      return [
         'nombre' =>
            sanitize_text_field(
               wp_unslash(
                  $_POST['nombre']
                  ?? ''
               )
            ),

         'whatsapp' =>
            sanitize_text_field(
               wp_unslash(
                  $_POST['whatsapp']
                  ?? ''
               )
            ),

         'email' =>
            sanitize_email(
               wp_unslash(
                  $_POST['email']
                  ?? ''
               )
            ),

         'mensaje' =>
            sanitize_textarea_field(
               wp_unslash(
                  $_POST['mensaje']
                  ?? ''
               )
            ),
      ];
   }

   /**
    * Valida los datos del formulario.
    *
    * @param array{
    *    nombre: string,
    *    whatsapp: string,
    *    email: string,
    *    mensaje: string
    * } $data
    *
    * @return array{
    *    valid: bool,
    *    message: string
    * }
    */
   private function validate(
      array $data
   ): array {
      if (
         $data['nombre'] === ''
         || $data['whatsapp'] === ''
         || $data['email'] === ''
         || $data['mensaje'] === ''
      ) {
         return [
            'valid' => false,
            'message' =>
               __(
                  'Todos los campos son obligatorios.',
                  'FWK'
               ),
         ];
      }

      if (
         !is_email(
            $data['email']
         )
      ) {
         return [
            'valid' => false,
            'message' =>
               __(
                  'El correo electrónico no es válido.',
                  'FWK'
               ),
         ];
      }

      return [
         'valid' => true,
         'message' => '',
      ];
   }

   /**
    * Envía el mensaje de contacto.
    *
    * @param array{
    *    nombre: string,
    *    whatsapp: string,
    *    email: string,
    *    mensaje: string
    * } $data
    */
   private function send(
      array $data
   ): bool {
      $to =
         get_option(
            'admin_email'
         );

      $subject =
         sprintf(
            __(
               'Nuevo mensaje de contacto de %s',
               'FWK'
            ),
            $data['nombre']
         );

      $message =
         sprintf(
            "Nombre: %s\n"
            . "WhatsApp: %s\n"
            . "Email: %s\n\n"
            . "Mensaje:\n%s",
            $data['nombre'],
            $data['whatsapp'],
            $data['email'],
            $data['mensaje']
         );

      $headers = [
         'Content-Type: text/plain; charset=UTF-8',
         sprintf(
            'Reply-To: %s <%s>',
            $data['nombre'],
            $data['email']
         ),
      ];

      return wp_mail(
         $to,
         $subject,
         $message,
         $headers
      );
   }
   /**
    * Procesa el formulario de contacto.
    *
    * @return array{
    *    success: bool,
    *    message: string
    * }
    */
   public function process(): array
   {
      if (!$this->is_submission()) {
         return [
            'success' => false,
            'message' => '',
         ];
      }

      if (!$this->verify_nonce()) {
         return [
            'success' => false,
            'message' =>
               __(
                  'No fue posible validar el formulario. Por favor, inténtelo nuevamente.',
                  'FWK'
               ),
         ];
      }

      $data =
         $this->get_data();

      $validation =
         $this->validate(
            $data
         );

      if (!$validation['valid']) {
         return [
            'success' => false,
            'message' =>
               $validation['message'],
         ];
      }

      $sent =
         $this->send(
            $data
         );

      if (!$sent) {
         return [
            'success' => false,
            'message' =>
               __(
                  'No fue posible enviar el mensaje. Por favor, inténtelo nuevamente.',
                  'FWK'
               ),
         ];
      }

      return [
         'success' => true,
         'message' =>
            __(
               'Su mensaje fue enviado correctamente.',
               'FWK'
            ),
      ];

      $redirectUrl =
         add_query_arg(
            'contact',
            'success',
            home_url('/')
         )
         . '#contacto';

      wp_safe_redirect(
         $redirectUrl
      );

      exit;

   }
}