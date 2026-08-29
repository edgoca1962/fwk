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
      $authorization =
         new PostAuthorizationService();

      return [
         'title' =>
            $this->get_blog_title(),

         'filters' =>
            $this->prepare_filters(),

         'actions' => [
            'create' =>
               $authorization->can_create(),
         ],
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

   public function prepare_create_form(): array
   {
      return [
         'form_action' =>
            admin_url('admin-post.php'),

         'action' =>
            'fwk_create_post',

         'nonce_action' =>
            'fwk_create_post',

         'nonce_name' =>
            'fwk_create_post_nonce',

         'title' =>
            __('Nuevo Artículo', 'FWK'),

         'fields' => [
            'title' => [
               'label' =>
                  __('Título', 'FWK'),

               'name' =>
                  'title',
            ],

            'content' => [
               'label' =>
                  __('Contenido', 'FWK'),

               'name' =>
                  'content',
            ],
         ],

         'submit_label' =>
            __('Publicar artículo', 'FWK'),
      ];
   }

   /**
    * Prepara las acciones disponibles
    * para un artículo.
    */
   public function prepare_post_actions(
      \WP_Post $post
   ): array {

      $authorization =
         new PostAuthorizationService();

      return [
         'edit' =>
            $authorization->can_edit(
               $post
            ),

         'delete' =>
            $authorization->can_delete(
               $post
            ),

         'delete_form_action' =>
            admin_url(
               'admin-post.php'
            ),

         'delete_action' =>
            'fwk_delete_post',

         'delete_nonce_action' =>
            'fwk_delete_post_'
            . $post->ID,

         'delete_nonce_name' =>
            'fwk_delete_post_nonce',
      ];
   }

   public function prepare_edit_form(
      int $postId
   ): array {

      $post =
         get_post(
            $postId
         );

      if (
         !$post instanceof \WP_Post
         || $post->post_type !== 'post'
      ) {
         return [
            'valid' => false,
            'authorized' => false,
            'title' => '',
            'post' => null,
         ];
      }
      $thumbnailId =
         get_post_thumbnail_id(
            $post->ID
         );

      $thumbnailUrl =
         $thumbnailId > 0
         ? wp_get_attachment_image_url(
            $thumbnailId,
            'medium_large'
         )
         : false;

      $authorization =
         new PostAuthorizationService();

      return [
         'valid' => true,

         'authorized' =>
            $authorization->can_edit(
               $post
            ),

         'title' =>
            sprintf(
               __(
                  'Editar Artículo: %s',
                  'FWK'
               ),
               get_the_title(
                  $post
               )
            ),

         'post' =>
            $post,

         'form_action' =>
            admin_url(
               'admin-post.php'
            ),

         'action' =>
            'fwk_update_post',

         'nonce_action' =>
            'fwk_update_post_' . $post->ID,

         'nonce_name' =>
            'fwk_update_post_nonce',

         'fields' => [
            'title' => [
               'label' =>
                  __('Título', 'FWK'),

               'name' =>
                  'title',

               'value' =>
                  $post->post_title,
            ],

            'content' => [
               'label' =>
                  __('Contenido', 'FWK'),

               'name' =>
                  'content',

               'value' =>
                  $post->post_content,
            ],
         ],

         'featured_image' => [
            'id' =>
               $thumbnailId,

            'url' =>
               is_string($thumbnailUrl)
               ? $thumbnailUrl
               : '',

            'label' =>
               __(
                  'Imagen destacada',
                  'FWK'
               ),

            'name' =>
               'featured_image',
         ],

         'submit_label' =>
            __(
               'Guardar cambios',
               'FWK'
            ),
      ];

   }
}