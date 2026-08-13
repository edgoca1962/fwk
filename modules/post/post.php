<?php

declare(strict_types=1);

namespace FWK\Modules\Post;

use FWK\Modules\Core\AbstractModule;
use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

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

   protected function register(): void
   {
      add_action(
         'wp_ajax_eliminar_post',
         [$this, 'eliminar_post']
      );

      /*
       * La creación de roles se migrará posteriormente.
       */
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