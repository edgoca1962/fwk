<?php

declare(strict_types=1);

namespace FWK\Modules\SGF\Service;

use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Servicio de propiedad y acceso a recursos SGF.
 *
 * Responsabilidades:
 *
 * - comprobar propietarios de posts SGF;
 * - validar relaciones movimiento → billetera;
 * - centralizar reglas de acceso;
 * - evitar que otros servicios repitan lógica de seguridad.
 *
 * @package FWK
 */
final class OwnershipService
{
   use Singleton;

   /**
    * CPT que pertenecen directamente a un usuario.
    *
    * @var string[]
    */
   private array $ownedPostTypes = [
      'billetera',
      'libro',
      'banco',
      'presupuesto',
   ];

   /**
    * CPT cuyo padre debe ser una billetera.
    *
    * @var string[]
    */
   private array $movementPostTypes = [
      'libro',
      'banco',
   ];

   protected function __construct()
   {
   }

   /**
    * Indica si el Post Type está sujeto a ownership SGF.
    */
   public function is_owned_post_type(
      string $postType
   ): bool {
      return in_array(
         sanitize_key($postType),
         $this->ownedPostTypes,
         true
      );
   }

   /**
    * Indica si el CPT representa un movimiento.
    */
   public function is_movement_post_type(
      string $postType
   ): bool {
      return in_array(
         sanitize_key($postType),
         $this->movementPostTypes,
         true
      );
   }

   /**
    * Devuelve el propietario directo del post.
    */
   public function get_owner_id(
      int $postId
   ): int {
      $post = get_post($postId);

      if (!$post instanceof \WP_Post) {
         return 0;
      }

      return (int) $post->post_author;
   }

   /**
    * Comprueba si un usuario es propietario del post.
    *
    * Si no se suministra userId utiliza el usuario actual.
    */
   public function is_owner(
      int $postId,
      ?int $userId = null
   ): bool {
      $userId ??= get_current_user_id();

      if ($userId <= 0) {
         return false;
      }

      return $this->get_owner_id($postId)
         === $userId;
   }

   /**
    * Comprueba si una billetera pertenece al usuario.
    */
   public function owns_wallet(
      int $walletId,
      ?int $userId = null
   ): bool {
      $wallet = get_post($walletId);

      if (
         !$wallet instanceof \WP_Post
         || $wallet->post_type !== 'billetera'
      ) {
         return false;
      }

      return $this->is_owner(
         $walletId,
         $userId
      );
   }

   /**
    * Valida la integridad de un movimiento.
    *
    * Regla:
    *
    * movimiento.post_author
    * =
    * billetera.post_author
    */
   public function validate_movement_ownership(
      int $movementId
   ): bool {
      $movement = get_post($movementId);

      if (
         !$movement instanceof \WP_Post
         || !$this->is_movement_post_type(
            $movement->post_type
         )
      ) {
         return false;
      }

      $walletId = (int) $movement->post_parent;

      if ($walletId <= 0) {
         return false;
      }

      $wallet = get_post($walletId);

      if (
         !$wallet instanceof \WP_Post
         || $wallet->post_type !== 'billetera'
      ) {
         return false;
      }

      return (int) $movement->post_author
         === (int) $wallet->post_author;
   }

   /**
    * Comprueba si el usuario puede acceder a un post SGF.
    *
    * Los administradores con edit_others_posts pueden
    * quedar exentos de la restricción de ownership.
    */
   public function can_access(
      int $postId,
      ?int $userId = null
   ): bool {
      $post = get_post($postId);

      if (!$post instanceof \WP_Post) {
         return false;
      }

      if (
         !$this->is_owned_post_type(
            $post->post_type
         )
      ) {
         return true;
      }

      $userId ??= get_current_user_id();

      if ($userId <= 0) {
         return false;
      }

      /*
       * Área administrativa / usuarios privilegiados.
       */
      if (
         $userId === get_current_user_id()
         && current_user_can(
            'edit_others_posts'
         )
      ) {
         return true;
      }

      if (
         !$this->is_owner(
            $postId,
            $userId
         )
      ) {
         return false;
      }

      /*
       * Para movimientos verificamos también
       * consistencia con la billetera padre.
       */
      if (
         $this->is_movement_post_type(
            $post->post_type
         )
      ) {
         return $this
            ->validate_movement_ownership(
               $postId
            );
      }

      return true;
   }

   /**
    * Valida que una billetera pueda utilizarse
    * como padre de un movimiento.
    */
   public function can_assign_wallet(
      int $walletId,
      ?int $userId = null
   ): bool {
      return $this->owns_wallet(
         $walletId,
         $userId
      );
   }

   /**
    * Devuelve argumentos de WP_Query limitados al usuario.
    *
    * @param array<string, mixed> $args
    *
    * @return array<string, mixed>
    */
   public function scope_query(
      array $args = [],
      ?int $userId = null
   ): array {
      $userId ??= get_current_user_id();

      if ($userId <= 0) {
         /*
          * Evita devolver accidentalmente registros
          * de todos los usuarios.
          */
         $args['post__in'] = [0];

         return $args;
      }

      $args['author'] = $userId;

      return $args;
   }
}