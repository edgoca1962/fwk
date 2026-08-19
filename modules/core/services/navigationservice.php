<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Administra comportamiento dinámico
 * de la navegación del Framework.
 */
final class NavigationService
{
   /**
    * Configuración global de la aplicación.
    *
    * @var array<string, mixed>
    */
   private array $config = [];

   public function __construct()
   {
      $appConfig =
         new AppConfigService();

      $this->config =
         $appConfig->all();
   }

   /**
    * Agrega Ingresar o Salir al menú público.
    */
   public function filter_public_menu_items(
      string $items,
      \stdClass $args
   ): string {
      if (
         ($args->theme_location ?? '')
         !== 'publico'
      ) {
         return $items;
      }

      if (is_user_logged_in()) {

         $publicHome = (string) (
            $this->config['public']['home']
            ?? '/'
         );

         $url =
            wp_logout_url(
               home_url(
                  $publicHome
               )
            );

         $label =
            __('Salir', 'FWK');

      } else {

         $loginSlug = (string) (
            $this->config['pages']['login']['slug']
            ?? 'login'
         );

         $url =
            home_url(
               '/' . trim(
                  $loginSlug,
                  '/'
               )
            );

         $label =
            __('Ingresar', 'FWK');
      }

      $items .= sprintf(
         '<li class="menu-item nav-item">'
         . '<a class="nav-link" href="%s">%s</a>'
         . '</li>',
         esc_url($url),
         esc_html($label)
      );

      return $items;
   }
   /**
    * Devuelve la ruta inicial correspondiente
    * al estado de autenticación actual.
    */
   public function get_home_url(): string
   {
      $home = is_user_logged_in()
         ? (string) (
            $this->config['authenticated']['home']
            ?? '/'
         )
         : (string) (
            $this->config['public']['home']
            ?? '/'
         );

      return home_url($home);
   }
}