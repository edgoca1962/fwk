<?php

namespace FWK\Modules\Core\Support;

use FWK\Modules\Core\Support\Singleton;
use FWK\Modules\Core\Services\CapabilityService;
use FWK\Modules\Core\Services\ProvisioningService;
use FWK\Modules\Core\Services\NavigationService;

/**
 * Configuración de Wordpress
 */
if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly.
}

class WPSetup
{
   use Singleton;
   private function __construct()
   {
      add_action('wp_enqueue_scripts', [$this, 'FWK_register_scripts_styles_local']);
      add_action('after_setup_theme', [$this, 'setup_theme']);
      add_action('after_switch_theme', [$this, 'provision_theme']);
      $navigation = new NavigationService();
      add_filter('wp_nav_menu_items', [$navigation, 'filter_public_menu_items'], 10, 2);
   }
   public function setup_theme(): void
   {
      load_theme_textdomain('FWK', get_template_directory() . '/languages');
      add_theme_support('title-tag');
      add_theme_support('automatic-feed-links');
      add_theme_support('post-thumbnails');
      add_theme_support('post-formats', ['aside', 'gallery', 'quote', 'image', 'video']);
      add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
      add_theme_support('customize-selective-refresh-widgets');
      add_theme_support('wp-block-styles');
      add_theme_support('block-templates');
      add_theme_support('align-wide');
      add_theme_support('custom-logo', ['height' => 300, 'width' => 300, 'flex-width' => true, 'flex-height' => true,]);
      register_nav_menus(
         [
            'principal' => __(
               'Menu Principal',
               'FWK'
            ),
            'publico' => __(
               'Menú público',
               'FWK'
            ),
            'modules_admin' => __(
               'Menú Administrador de módulos',
               'FWK'
            ),
            'general_admin' => __(
               'Menú Administrador General',
               'FWK'
            ),
            'wp_admin' => __(
               'Menú Administrador de Wordpress',
               'FWK'
            ),
         ]
      );
      $capabilities =
         new CapabilityService();

      $capabilities
         ->register_base_roles();

      $capabilities
         ->register_base_capabilities();
   }
   public function FWK_register_scripts_styles_local()
   {
      $font_families = [
         'family=Syne:wght@400..800',
         'family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900',
         'family=Roboto+Condensed:ital,wght@0,100..900;1,100..900',
         'family=Roboto:ital,wght@0,100..900;1,100..900'
      ];
      wp_enqueue_style('sgf-combined-fonts', "https://fonts.googleapis.com/css2?" . implode('&', $font_families) . '&display=swap', [], null);
      wp_enqueue_style('main-styles', get_stylesheet_uri(), [], wp_get_theme()->get('Version'), 'all');

      wp_enqueue_script('main-script', get_template_directory_uri() . '/assets/main.js', [], wp_get_theme()->get('Version'), true);

      wp_localize_script(
         'main-script',
         'FWK_AJAX',
         [
            'endpoint' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('post_abc'),
            'home' => esc_url('/')
         ]
      );
   }
   /**
    * Provisiona la estructura base del tema
    * cuando FWK es activado.
    */
   public function provision_theme(): void
   {
      $provisioning =
         new ProvisioningService();

      $provisioning->provision();
   }
}
