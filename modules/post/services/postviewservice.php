<?php

declare(strict_types=1);

namespace FWK\Modules\Post\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Prepara datos de presentación
 * para las vistas del módulo Post.
 */
final class PostViewService
{
   /**
    * Devuelve el encabezado correspondiente
    * al contexto actual del blog.
    */
   public function get_blog_title(): string
   {
      if (is_home()) {
         return __(
            'Blog',
            'FWK'
         );
      }

      if (is_category()) {
         return single_cat_title(
            '',
            false
         );
      }

      if (is_tag()) {
         return single_tag_title(
            '',
            false
         );
      }

      if (is_search()) {
         return sprintf(
            __(
               'Resultados para: %s',
               'FWK'
            ),
            get_search_query()
         );
      }

      if (is_archive()) {
         return get_the_archive_title();
      }

      return '';
   }

   /**
    * Prepara los datos de presentación
    * para la vista principal del blog.
    *
    * @return array{
    *    title: string
    * }
    */
   public function prepare_blog_page(): array
   {
      return [
         'title' =>
            $this->get_blog_title(),
      ];
   }
}