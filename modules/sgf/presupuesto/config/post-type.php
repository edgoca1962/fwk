<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'enabled' => false, // conserva tu valor real

   'singular' => 'Presupuesto',
   'plural' => 'Presupuestos',

   'supports' => [
      'title',
      'author',
      'custom-fields',
   ],

   'taxonomies' => [
      'sgf_igt',
   ],

   'resources' => [
      /*
       * Futuro:
       *
       * 'meta' =>
       *    'presupuesto/config/meta.php',
       *
       * 'metaboxes' =>
       *    'presupuesto/config/metaboxes.php',
       */
   ],

   'args' => [
      'public' => true,
      'publicly_queryable' => true,

      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nav_menus' => true,
      'show_in_admin_bar' => true,

      'show_in_rest' => true,

      'has_archive' => true,

      'hierarchical' => false,

      'exclude_from_search' => false,

      'query_var' => true,

      'menu_icon' => 'dashicons-chart-bar',

      'rewrite' => [
         'slug' => 'presupuestos',
         'with_front' => false,
      ],
   ],
];