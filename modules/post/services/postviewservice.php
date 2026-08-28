<?php

declare(strict_types=1);

namespace FWK\Modules\Post\Services;

if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Core\Services\FilterConfigService;
use FWK\Modules\Core\Services\FilterRequestService;

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
            __('Resultados para: %s', 'FWK'),
            get_search_query()
         );
      }

      if (is_day()) {
         $year = (int) get_query_var('year');
         $month = (int) get_query_var('monthnum');
         $day = (int) get_query_var('day');

         return sprintf(
            '%02d/%02d/%04d',
            $day,
            $month,
            $year
         );
      }

      if (is_month()) {
         global $wp_locale;

         $year = (int) get_query_var('year');
         $month = (int) get_query_var('monthnum');

         return sprintf(
            '%s %d',
            $wp_locale->get_month($month),
            $year
         );
      }

      if (is_year()) {
         return (string) get_query_var(
            'year'
         );
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

         'filters' =>
            $this->prepare_filters(),
      ];
   }

   /**
    * Prepara los datos necesarios
    * para los filtros del Blog.
    *
    * @return array<string, mixed>
    */
   private function prepare_filters(): array
   {
      $configService =
         new FilterConfigService();

      $requestService =
         new FilterRequestService();

      $config =
         $configService
            ->load_for_post_type(
               'post'
            );

      $currentUrl = strtok(
         home_url(
            add_query_arg(
               [],
               $_SERVER['REQUEST_URI'] ?? '/'
            )
         ),
         '?'
      );

      $currentUrl = preg_replace(
         '#/page/\d+/?$#',
         '/',
         $currentUrl
      );


      return [
         'post_type' =>
            'post',

         'base_url' =>
            $currentUrl,

         'config' =>
            $config,

         'filters' =>
            $requestService->resolve(
               $config
            ),
      ];
   }
}