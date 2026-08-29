<?php

declare(strict_types=1);

namespace FWK\Modules\Post;

if (!defined('ABSPATH')) {
   exit;
}

use FWK\Modules\Core\AbstractModule;
use FWK\Modules\Core\Support\Singleton;
use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Services\FilterConfigService;
use FWK\Modules\Core\Services\FilterQueryService;
use FWK\Modules\Core\Services\FilterRequestService;
use FWK\Modules\Post\Services\PostAuthorizationService;
use FWK\Modules\Post\Services\PostCrudService;


/**
 * Módulo para el blog nativo de WordPress.
 *
 * @package FWK
 */
final class Post extends AbstractModule
{
   use Singleton;

   /**
    * Determina si el módulo Post
    * puede atender la solicitud actual.
    */
   public function supports(
      RequestContext $request
   ): bool {

      /*
       * La página configurada por WordPress
       * como índice de entradas pertenece
       * al módulo Post.
       */
      if ($request->is_home()) {
         return true;
      }

      return parent::supports(
         $request
      );
   }
   protected function register(): void
   {
      add_action(
         'pre_get_posts',
         [$this, 'apply_filters_to_query']
      );

      add_action(
         'admin_post_fwk_create_post',
         [$this, 'create_post']
      );

      add_action(
         'admin_post_fwk_update_post',
         [$this, 'update_post']
      );

      add_action(
         'admin_post_fwk_delete_post',
         [$this, 'delete_post']
      );

   }

   /**
    * Aplica los filtros configurados
    * a la consulta principal del Blog.
    */
   public function apply_filters_to_query(
      \WP_Query $query
   ): void {
      if (
         is_admin()
         || !$query->is_main_query()
      ) {
         return;
      }

      if (
         !$query->is_home()
         && !$query->is_post_type_archive('post')
         && !$query->is_category()
         && !$query->is_tag()
         && !$query->is_date()
      ) {
         return;
      }

      $configService =
         new FilterConfigService();

      $requestService =
         new FilterRequestService();

      $queryService =
         new FilterQueryService();

      $config =
         $configService
            ->load_for_post_type(
               'post'
            );

      if ($config === []) {
         return;
      }

      $filters =
         $requestService->resolve(
            $config
         );

      $args =
         $queryService->build(
            'post',
            $filters
         );

      foreach ($args as $key => $value) {
         $query->set(
            $key,
            $value
         );
      }
   }

   public function create_post(): void
   {
      if (!is_user_logged_in()) {
         wp_die(
            esc_html__(
               'Debes iniciar sesión para crear artículos.',
               'FWK'
            )
         );
      }

      $authorization =
         new PostAuthorizationService();

      if (!$authorization->can_create()) {
         wp_die(
            esc_html__(
               'No tienes autorización para crear artículos.',
               'FWK'
            ),
            '',
            [
               'response' => 403,
            ]
         );
      }

      check_admin_referer(
         'fwk_create_post',
         'fwk_create_post_nonce'
      );

      $service =
         new PostCrudService();

      $result =
         $service->create($_POST);

      if (is_wp_error($result)) {
         wp_die(
            esc_html(
               $result->get_error_message()
            )
         );
      }

      $url =
         get_permalink($result);

      if (!is_string($url)) {
         $url = home_url('/blog/');
      }

      wp_safe_redirect($url);
      exit;
   }

   public function update_post(): void
   {
      if (
         !is_user_logged_in()
      ) {
         wp_die(
            esc_html__(
               'Debes iniciar sesión para editar artículos.',
               'FWK'
            ),
            '',
            [
               'response' => 401,
            ]
         );
      }

      $postId =
         isset($_POST['post_id'])
         ? absint(
            $_POST['post_id']
         )
         : 0;

      if ($postId <= 0) {
         wp_die(
            esc_html__(
               'El artículo solicitado no es válido.',
               'FWK'
            ),
            '',
            [
               'response' => 400,
            ]
         );
      }

      $post =
         get_post(
            $postId
         );

      if (
         !$post instanceof \WP_Post
         || $post->post_type !== 'post'
      ) {
         wp_die(
            esc_html__(
               'El artículo solicitado no existe.',
               'FWK'
            ),
            '',
            [
               'response' => 404,
            ]
         );
      }

      $authorization =
         new PostAuthorizationService();

      if (
         !$authorization->can_edit(
            $post
         )
      ) {
         wp_die(
            esc_html__(
               'No tienes autorización para editar este artículo.',
               'FWK'
            ),
            '',
            [
               'response' => 403,
            ]
         );
      }

      check_admin_referer(
         'fwk_update_post_'
         . $postId,
         'fwk_update_post_nonce'
      );

      $service =
         new PostCrudService();

      $result =
         $service->update(
            $postId,
            $_POST
         );

      if (
         is_wp_error(
            $result
         )
      ) {
         wp_die(
            esc_html(
               $result
                  ->get_error_message()
            )
         );
      }

      $url =
         get_permalink(
            $result
         );

      if (
         !is_string($url)
         || $url === ''
      ) {
         $url =
            home_url(
               '/blog/'
            );
      }

      wp_safe_redirect(
         $url
      );

      exit;
   }

   public function delete_post(): void
   {
      if (
         !is_user_logged_in()
      ) {
         wp_die(
            esc_html__(
               'Debes iniciar sesión para eliminar artículos.',
               'FWK'
            ),
            '',
            [
               'response' => 401,
            ]
         );
      }

      $postId =
         isset($_POST['post_id'])
         ? absint(
            $_POST['post_id']
         )
         : 0;

      if ($postId <= 0) {
         wp_die(
            esc_html__(
               'El artículo solicitado no es válido.',
               'FWK'
            ),
            '',
            [
               'response' => 400,
            ]
         );
      }

      $post =
         get_post(
            $postId
         );

      if (
         !$post instanceof \WP_Post
         || $post->post_type !== 'post'
      ) {
         wp_die(
            esc_html__(
               'El artículo solicitado no existe.',
               'FWK'
            ),
            '',
            [
               'response' => 404,
            ]
         );
      }

      $authorization =
         new PostAuthorizationService();

      if (
         !$authorization->can_delete(
            $post
         )
      ) {
         wp_die(
            esc_html__(
               'No tienes autorización para eliminar este artículo.',
               'FWK'
            ),
            '',
            [
               'response' => 403,
            ]
         );
      }

      check_admin_referer(
         'fwk_delete_post_'
         . $postId,
         'fwk_delete_post_nonce'
      );

      $service =
         new PostCrudService();

      $result =
         $service->delete(
            $postId
         );

      if (
         is_wp_error(
            $result
         )
      ) {
         wp_die(
            esc_html(
               $result
                  ->get_error_message()
            )
         );
      }

      wp_safe_redirect(
         home_url(
            '/blog/'
         )
      );

      exit;
   }

}
