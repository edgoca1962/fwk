<?php

namespace FWK\Modules\Core\Support;

use FWK\Modules\Core\Support\Singleton;

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
      add_action('wp_enqueue_scripts', [$this, 'SGF_register_scripts_styles_local']);
      add_action('after_setup_theme', [$this, 'setup_theme']);

   }
   public function setup_theme(): void
   {
      load_theme_textdomain('FWK', get_template_directory() . '/languages');
      add_theme_support('title-tag');
      add_theme_support('automatic-feed-links');
      add_theme_support('post-thumbnails');
      add_theme_support('post-formats', array('aside', 'gallery', 'quote', 'image', 'video'));
      add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
      add_theme_support('customize-selective-refresh-widgets');
      add_theme_support('wp-block-styles');
      add_theme_support('block-templates');
      add_theme_support('align-wide');
      add_theme_support('custom-logo', array('height' => 300, 'width' => 300, 'flex-width' => true, 'flex-height' => true, ));
      register_nav_menus(
         array(
            'principal' => __('Menu Principal', 'FWK'),
            'administrador' => __('Menu Administrador', 'FWK'),
            'logout' => __('Menu Loged Out', 'FWK'),
         )
      );

      update_option('adminRoles', false);
      if (!get_option('adminRoles')) {
         $base_caps = get_role('subscriber')->capabilities;
         $base_caps['useradmingeneral'] = true;
         add_role('useradmingeneral', 'Administrador(a) General', $base_caps);
      }
   }
   public function SGF_register_scripts_styles_local()
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
         'SGF_AJAX',
         [
            'endpoint' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('post_abc'),
            'home' => esc_url('/')
         ]
      );
   }
}
