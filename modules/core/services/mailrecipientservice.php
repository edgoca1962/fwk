<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Resuelve destinatarios de correo
 * según el contexto administrativo.
 */
final class MailRecipientService
{
   /**
    * Resuelve un destinatario.
    *
    * @param array<string, mixed> $context
    */
   public function resolve(
      string $type,
      array $context = []
   ): string {
      return match ($type) {

         'wp_admin' =>
         $this->resolve_wp_admin(),

         'general_admin' =>
         $this->resolve_general_admin(),

         'module_admin' =>
         $this->resolve_module_admin(
            $context
         ),

         'post_admin' =>
         $this->resolve_post_admin(
            $context
         ),

         default =>
         '',
      };
   }

   /**
    * Devuelve el correo administrativo
    * configurado en WordPress.
    */
   private function resolve_wp_admin(): string
   {
      return sanitize_email(
         (string) get_option(
            'admin_email'
         )
      );
   }

   /**
    * Devuelve el correo del administrador
    * general del framework.
    */
   private function resolve_general_admin(): string
   {
      $users =
         get_users([
            'role' =>
               'fwk_general_admin',

            'number' =>
               1,

            'fields' => [
               'user_email',
            ],
         ]);

      if ($users === []) {
         return '';
      }

      return sanitize_email(
         (string) (
            $users[0]->user_email
            ?? ''
         )
      );
   }

   /**
    * Resuelve el administrador
    * responsable de un módulo.
    *
    * @param array<string, mixed> $context
    */
   private function resolve_module_admin(
      array $context
   ): string {
      /*
       * Lo implementaremos cuando definamos
       * formalmente administradores por módulo.
       */
      return '';
   }

   /**
    * Resuelve el responsable
    * de un post concreto.
    *
    * @param array<string, mixed> $context
    */
   private function resolve_post_admin(
      array $context
   ): string {
      $postId =
         (int) (
            $context['post_id']
            ?? 0
         );

      if ($postId <= 0) {
         return '';
      }

      $post =
         get_post(
            $postId
         );

      if (!$post instanceof \WP_Post) {
         return '';
      }

      $user =
         get_user_by(
            'id',
            (int) $post->post_author
         );

      if (!$user instanceof \WP_User) {
         return '';
      }

      return sanitize_email(
         (string) $user->user_email
      );
   }
}