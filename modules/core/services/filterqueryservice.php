<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Convierte el estado normalizado de filtros
 * en argumentos compatibles con WP_Query.
 */
final class FilterQueryService
{
   /**
    * Construye argumentos de consulta
    * a partir de los filtros resueltos.
    *
    * @param array{
    *    search?: string,
    *    taxonomies?: array<string, string>,
    *    order?: string,
    *    paged?: int
    * } $filters
    *
    * @return array<string, mixed>
    */
   public function build(
      string $postType,
      array $filters
   ): array {
      $postType =
         sanitize_key(
            $postType
         );

      if ($postType === '') {
         return [];
      }

      $args = [
         'post_type' =>
            $postType,

         'order' =>
            $this->resolve_order(
               $filters
            ),

         'paged' =>
            $this->resolve_paged(
               $filters
            ),

         'ignore_sticky_posts' => true,
      ];

      $search =
         $filters['search']
         ?? '';

      if (
         is_string($search)
         && $search !== ''
      ) {
         $args['s'] =
            $search;
      }

      $taxQuery =
         $this->build_tax_query(
            $filters['taxonomies']
            ?? []
         );

      if ($taxQuery !== []) {
         $args['tax_query'] =
            $taxQuery;
      }

      return $args;
   }

   /**
    * Construye tax_query.
    *
    * @param array<string, string> $taxonomies
    *
    * @return array<int|string, mixed>
    */
   private function build_tax_query(
      array $taxonomies
   ): array {
      $taxQuery = [];

      foreach (
         $taxonomies
         as $taxonomy => $term
      ) {
         $taxonomy =
            sanitize_key(
               (string) $taxonomy
            );

         $term =
            sanitize_title(
               (string) $term
            );

         if (
            $taxonomy === ''
            || $term === ''
         ) {
            continue;
         }

         $taxQuery[] = [
            'taxonomy' =>
               $taxonomy,

            'field' =>
               'slug',

            'terms' =>
               $term,
         ];
      }

      if (
         count($taxQuery) > 1
      ) {
         $taxQuery['relation'] =
            'AND';
      }

      return $taxQuery;
   }

   /**
    * Devuelve un orden válido.
    *
    * @param array<string, mixed> $filters
    */
   private function resolve_order(
      array $filters
   ): string {
      $order =
         strtoupper(
            (string) (
               $filters['order']
               ?? 'DESC'
            )
         );

      return in_array(
         $order,
         [
            'ASC',
            'DESC',
         ],
         true
      )
         ? $order
         : 'DESC';
   }

   /**
    * Devuelve una página válida.
    *
    * @param array<string, mixed> $filters
    */
   private function resolve_paged(
      array $filters
   ): int {
      return max(
         1,
         (int) (
            $filters['paged']
            ?? 1
         )
      );
   }
}