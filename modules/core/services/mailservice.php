<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Centraliza el envío de correos del framework.
 */
final class MailService
{
   /**
    * Configuración global de la aplicación.
    *
    * @var array<string, mixed>
    */
   private array $config = [];

   public function __construct()
   {
      $appConfig =
         new AppConfigService();

      $this->config =
         $appConfig->all();
   }

   /**
    * Envía un correo utilizando la configuración
    * global del framework.
    *
    * @param array{
    *    to: string,
    *    subject: string,
    *    message: string,
    *    reply_to_name?: string,
    *    reply_to_email?: string
    * } $data
    */
   public function send(
      array $data
   ): bool {
      $to =
         sanitize_email(
            $data['to']
            ?? ''
         );

      if (
         $to === ''
         || !is_email($to)
      ) {
         return false;
      }

      $fromEmail =
         sanitize_email(
            (string) (
               $this->config['mail']['from_email']
               ?? ''
            )
         );

      if (
         $fromEmail === ''
         || !is_email($fromEmail)
      ) {
         return false;
      }

      $headers = [
         'Content-Type: text/plain; charset=UTF-8',

         sprintf(
            'From: %s <%s>',
            get_bloginfo('name'),
            $fromEmail
         ),
      ];

      $replyToEmail =
         sanitize_email(
            $data['reply_to_email']
            ?? ''
         );

      if (
         $replyToEmail !== ''
         && is_email($replyToEmail)
      ) {
         $replyToName =
            sanitize_text_field(
               $data['reply_to_name']
               ?? ''
            );

         $headers[] =
            sprintf(
               'Reply-To: %s <%s>',
               $replyToName,
               $replyToEmail
            );
      }

      return wp_mail(
         $to,
         sanitize_text_field(
            $data['subject']
            ?? ''
         ),
         (string) (
            $data['message']
            ?? ''
         ),
         $headers
      );
   }
}