<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

use FWK\Modules\Core\Registry\ModuleRegistry;
use FWK\Modules\Core\Registry\PostTypeRegistry;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Carga y normaliza la configuración
 * de filtros de un recurso.
 */
final class FilterConfigService
{
   /**
    * Carga una configuración de filtros.
    *
    * @return array<string, mixed>
    */
   public function load(
      string $file
   ): array {
      if (!is_readable($file)) {
         return [];
      }

      $config =
         require $file;

      if (!is_array($config)) {
         return [];
      }

      return $this->normalize(
         $config
      );
   }

   /**
    * Normaliza la estructura mínima
    * esperada del contrato.
    *
    * @param array<string, mixed> $config
    *
    * @return array<string, mixed>
    */
   private function normalize(
      array $config
   ): array {
      return [
         'search' =>
            is_array(
               $config['search']
               ?? null
            )
            ? $config['search']
            : [],

         'taxonomies' =>
            is_array(
               $config['taxonomies']
               ?? null
            )
            ? $config['taxonomies']
            : [],

         'order' =>
            is_array(
               $config['order']
               ?? null
            )
            ? $config['order']
            : [],

         'pagination' =>
            is_array(
               $config['pagination']
               ?? null
            )
            ? $config['pagination']
            : [],
      ];
   }

   /**
    * Carga la configuración de filtros
    * asociada a un Post Type.
    *
    * @return array<string, mixed>
    */
   public function load_for_post_type(
      string $postType
   ): array {
      $postType =
         sanitize_key(
            $postType
         );

      if ($postType === '') {
         return [];
      }

      $postTypes =
         PostTypeRegistry::get_instance();

      $definition =
         $postTypes->get(
            $postType
         );

      if (
         $definition === null
         || !$definition->has_resource(
            'filters'
         )
      ) {
         return [];
      }

      $resource =
         $definition->get_resource(
            'filters'
         );

      if (!is_string($resource)) {
         return [];
      }

      $modules =
         ModuleRegistry::get_instance();

      $module =
         $modules->find_by_post_type(
            $postType
         );

      if ($module === null) {
         return [];
      }

      $file =
         $module
            ->manifest()
            ->get_directory()
         . '/'
         . ltrim(
            wp_normalize_path(
               $resource
            ),
            '/'
         );

      return $this->load(
         $file
      );
   }
}