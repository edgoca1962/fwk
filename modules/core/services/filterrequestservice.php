<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Lee, sanitiza y normaliza los filtros
 * recibidos mediante parámetros GET.
 */
final class FilterRequestService
{
   /**
    * Resuelve el estado actual de filtros
    * a partir de su configuración.
    *
    * @param array<string, mixed> $config
    *
    * @return array{
    *    search: string,
    *    taxonomies: array<string, string>,
    *    order: string,
    *    paged: int
    * }
    */
   public function resolve(
      array $config
   ): array {
      return [
         'search' =>
            $this->resolve_search(
               $config['search']
               ?? []
            ),

         'taxonomies' =>
            $this->resolve_taxonomies(
               $config['taxonomies']
               ?? []
            ),

         'order' =>
            $this->resolve_order(
               $config['order']
               ?? []
            ),

         'paged' =>
            $this->resolve_paged(
               $config['pagination']
               ?? []
            ),
      ];
   }

   /**
    * Resuelve el filtro de búsqueda.
    *
    * @param array<string, mixed> $config
    */
   private function resolve_search(
      array $config
   ): string {
      if (
         empty($config['enabled'])
      ) {
         return '';
      }

      $param =
         sanitize_key(
            (string) (
               $config['param']
               ?? ''
            )
         );

      if (
         $param === ''
         || !isset($_GET[$param])
      ) {
         return '';
      }

      return sanitize_text_field(
         wp_unslash(
            (string) $_GET[$param]
         )
      );
   }

   /**
    * Resuelve filtros por taxonomía.
    *
    * @param array<string, mixed> $config
    *
    * @return array<string, string>
    */
   private function resolve_taxonomies(
      array $config
   ): array {
      $filters = [];

      foreach (
         $config
         as $taxonomy => $definition
      ) {
         if (
            !is_array($definition)
            || empty(
            $definition['enabled']
         )
         ) {
            continue;
         }

         $param =
            sanitize_key(
               (string) (
                  $definition['param']
                  ?? ''
               )
            );

         if (
            $param === ''
            || !isset($_GET[$param])
         ) {
            continue;
         }

         $value =
            sanitize_title(
               wp_unslash(
                  (string) $_GET[$param]
               )
            );

         if ($value === '') {
            continue;
         }

         $filters[
            sanitize_key(
               (string) $taxonomy
            )
         ] =
            $value;
      }

      return $filters;
   }

   /**
    * Resuelve el orden solicitado.
    *
    * @param array<string, mixed> $config
    */
   private function resolve_order(
      array $config
   ): string {
      $default =
         strtoupper(
            (string) (
               $config['default']
               ?? 'DESC'
            )
         );

      if (
         empty($config['enabled'])
      ) {
         return $default;
      }

      $param =
         sanitize_key(
            (string) (
               $config['param']
               ?? ''
            )
         );

      if (
         $param === ''
         || !isset($_GET[$param])
      ) {
         return $default;
      }

      $requested =
         strtoupper(
            sanitize_text_field(
               wp_unslash(
                  (string) $_GET[$param]
               )
            )
         );

      $allowed =
         is_array(
            $config['allowed']
            ?? null
         )
         ? array_map(
            'strtoupper',
            $config['allowed']
         )
         : [
            'DESC',
            'ASC',
         ];

      return in_array(
         $requested,
         $allowed,
         true
      )
         ? $requested
         : $default;
   }

   /**
    * Resuelve la página actual.
    *
    * @param array<string, mixed> $config
    */
   private function resolve_paged(
      array $config
   ): int {
      $default =
         max(
            1,
            (int) (
               $config['default']
               ?? 1
            )
         );

      if (
         empty($config['enabled'])
      ) {
         return $default;
      }

      $param =
         sanitize_key(
            (string) (
               $config['param']
               ?? 'paged'
            )
         );

      /*
       * Si existe paged explícitamente
       * en GET, tiene prioridad.
       */
      if (
         $param !== ''
         && isset($_GET[$param])
      ) {
         return max(
            1,
            absint(
               wp_unslash(
                  $_GET[$param]
               )
            )
         );
      }

      /*
       * Si WordPress ya resolvió una URL
       * como /blog/page/2/, respetamos
       * su query var.
       */
      $paged =
         absint(
            get_query_var(
               'paged'
            )
         );

      if ($paged > 0) {
         return $paged;
      }

      return $default;
   }
}