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

/**
 * Módulo para el blog nativo de WordPress.
 *
 * @package FWK
 */
final class Post extends AbstractModule
{
   use Singleton;

   protected function __construct()
   {
   }

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
         'wp_ajax_eliminar_post',
         [$this, 'eliminar_post']
      );

      /*
       * La creación de roles se migrará posteriormente.
       */
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
   public function eliminar_post(): void
   {
      check_ajax_referer(
         'post_abc',
         'nonce'
      );

      if (!current_user_can('delete_posts')) {
         wp_send_json_error(
            [
               'titulo' => __('Permiso denegado', 'FWK'),
               'msg' => __(
                  'No tienes autorización para eliminar artículos.',
                  'FWK'
               ),
            ],
            403
         );
      }

      wp_send_json_success([
         'titulo' => __('Artículo eliminado', 'FWK'),
         'msg' => __(
            'El artículo se eliminó correctamente.',
            'FWK'
         ),
         'action' => 'eliminar',
      ]);
   }
}
