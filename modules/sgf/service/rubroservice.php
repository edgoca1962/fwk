<?php

declare(strict_types=1);

namespace FWK\Modules\SGF\Service;

use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Gestiona la jerarquía y propiedad
 * de los Rubros financieros de SGF.
 *
 * Taxonomía:
 * sgf_igt
 *
 * Jerarquía:
 * Nivel 1 → Tipo de categoría
 * Nivel 2 → Categoría
 * Nivel 3 → Subcategoría
 */
final class RubroService
{
   use Singleton;

   private const TAXONOMY = 'sgf_igt';

   protected function __construct()
   {
   }

   /**
    * Devuelve los Rubros de nivel 1
    * pertenecientes a un usuario.
    *
    * @return \WP_Term[]
    */
   public function get_roots(
      ?int $userId = null
   ): array {
      $userId ??= get_current_user_id();

      if ($userId <= 0) {
         return [];
      }

      $terms = get_terms([
         'taxonomy' => self::TAXONOMY,
         'parent' => 0,
         'hide_empty' => false,
         'meta_query' => [
            [
               'key' => 'user_id',
               'value' => $userId,
               'compare' => '=',
               'type' => 'NUMERIC',
            ],
         ],
      ]);

      return is_wp_error($terms)
         ? []
         : $terms;
   }

   /**
    * Devuelve los hijos directos de un Rubro.
    *
    * Solo devuelve términos pertenecientes
    * al usuario indicado.
    *
    * @return \WP_Term[]
    */
   public function get_children(
      int $parentId,
      ?int $userId = null
   ): array {
      $userId ??= get_current_user_id();

      if (
         $parentId <= 0
         || $userId <= 0
      ) {
         return [];
      }

      /*
       * El padre también debe pertenecer
       * al usuario.
       */
      if (
         !$this->belongs_to_user(
            $parentId,
            $userId
         )
      ) {
         return [];
      }

      $terms = get_terms([
         'taxonomy' => self::TAXONOMY,
         'parent' => $parentId,
         'hide_empty' => false,
         'meta_query' => [
            [
               'key' => 'user_id',
               'value' => $userId,
               'compare' => '=',
               'type' => 'NUMERIC',
            ],
         ],
      ]);

      return is_wp_error($terms)
         ? []
         : $terms;
   }

   /**
    * Comprueba si un término pertenece
    * al usuario indicado.
    */
   public function belongs_to_user(
      int $termId,
      ?int $userId = null
   ): bool {
      $userId ??= get_current_user_id();

      if (
         $termId <= 0
         || $userId <= 0
      ) {
         return false;
      }

      $term = get_term(
         $termId,
         self::TAXONOMY
      );

      if (
         !$term instanceof \WP_Term
      ) {
         return false;
      }

      return (int) get_term_meta(
         $termId,
         'user_id',
         true
      ) === $userId;
   }

   /**
    * Devuelve el nivel jerárquico
    * de un Rubro.
    *
    * 0 = término inválido
    * 1 = Tipo de categoría
    * 2 = Categoría
    * 3 = Subcategoría
    */
   public function get_level(
      int $termId
   ): int {
      if ($termId <= 0) {
         return 0;
      }

      $term = get_term(
         $termId,
         self::TAXONOMY
      );

      if (
         !$term instanceof \WP_Term
      ) {
         return 0;
      }

      if ((int) $term->parent === 0) {
         return 1;
      }

      $parent = get_term(
         (int) $term->parent,
         self::TAXONOMY
      );

      if (
         !$parent instanceof \WP_Term
      ) {
         return 0;
      }

      if ((int) $parent->parent === 0) {
         return 2;
      }

      return 3;
   }

   /**
    * Busca un término del usuario por
    * nombre y padre.
    *
    * Importante:
    * el slug NO se considera identificador
    * único dentro de SGF.
    */
   public function term_exists_for_user(
      string $name,
      int $parentId,
      ?int $userId = null
   ): ?int {
      $userId ??= get_current_user_id();

      if (
         $name === ''
         || $parentId < 0
         || $userId <= 0
      ) {
         return null;
      }

      $terms = get_terms([
         'taxonomy' => self::TAXONOMY,
         'parent' => $parentId,
         'hide_empty' => false,
         'name' => $name,
         'meta_query' => [
            [
               'key' => 'user_id',
               'value' => $userId,
               'compare' => '=',
               'type' => 'NUMERIC',
            ],
         ],
      ]);

      if (
         is_wp_error($terms)
         || $terms === []
      ) {
         return null;
      }

      return (int) $terms[0]->term_id;
   }

   /**
    * Valida una ruta jerárquica.
    *
    * Los valores 0 representan
    * "Sin selección".
    */
   public function validate_path(
      int $typeId,
      int $categoryId = 0,
      int $subcategoryId = 0,
      ?int $userId = null
   ): bool {
      $userId ??= get_current_user_id();

      if ($userId <= 0) {
         return false;
      }

      /*
       * Sin Tipo implica que los demás
       * niveles también deben estar vacíos.
       */
      if ($typeId === 0) {
         return $categoryId === 0
            && $subcategoryId === 0;
      }

      if (
         !$this->belongs_to_user(
            $typeId,
            $userId
         )
         || $this->get_level($typeId) !== 1
      ) {
         return false;
      }

      /*
       * Sin Categoría implica que tampoco
       * puede existir Subcategoría.
       */
      if ($categoryId === 0) {
         return $subcategoryId === 0;
      }

      if (
         !$this->belongs_to_user(
            $categoryId,
            $userId
         )
      ) {
         return false;
      }

      $category = get_term(
         $categoryId,
         self::TAXONOMY
      );

      if (
         !$category instanceof \WP_Term
         || (int) $category->parent !== $typeId
      ) {
         return false;
      }

      if ($subcategoryId === 0) {
         return true;
      }

      if (
         !$this->belongs_to_user(
            $subcategoryId,
            $userId
         )
      ) {
         return false;
      }

      $subcategory = get_term(
         $subcategoryId,
         self::TAXONOMY
      );

      if (
         !$subcategory instanceof \WP_Term
      ) {
         return false;
      }

      return (int) $subcategory->parent
         === $categoryId;
   }
}