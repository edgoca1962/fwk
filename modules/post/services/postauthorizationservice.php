<?php

declare(strict_types=1);

namespace FWK\Modules\Post\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Centraliza las reglas de autorización
 * para las operaciones CRUD de artículos.
 */
final class PostAuthorizationService
{
   /**
    * Indica si el usuario actual puede
    * crear artículos.
    */
   public function can_create(): bool
   {
      if ($this->is_wordpress_administrator()) {
         return true;
      }

      return (
         $this->has_role('fwk_admin_blog')
         || $this->has_role('fwk_admin_post')
      );
   }

   /**
    * Indica si el usuario actual puede
    * editar un artículo.
    */
   public function can_edit(
      \WP_Post $post
   ): bool {

      if ($this->is_wordpress_administrator()) {
         return true;
      }

      if ($this->has_role('fwk_admin_blog')) {
         return true;
      }

      if (!$this->has_role('fwk_admin_post')) {
         return false;
      }

      return (
         (int) $post->post_author
         === get_current_user_id()
      );
   }

   /**
    * Indica si el usuario actual puede
    * eliminar un artículo.
    */
   public function can_delete(
      \WP_Post $post
   ): bool {

      if ($this->is_wordpress_administrator()) {
         return true;
      }

      return $this->has_role(
         'fwk_admin_blog'
      );
   }

   /**
    * Comprueba si el usuario actual
    * posee un rol determinado.
    */
   private function has_role(
      string $role
   ): bool {

      $user = wp_get_current_user();

      if ($user->ID <= 0) {
         return false;
      }

      return in_array(
         $role,
         (array) $user->roles,
         true
      );
   }

   /**
    * El administrador nativo de WordPress
    * actúa como superusuario de WP FRW.
    */
   private function is_wordpress_administrator(): bool
   {
      return $this->has_role(
         'administrator'
      );
   }
}