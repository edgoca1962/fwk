<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'enabled' => false, // conserva el valor que tengas actualmente

   'singular' => 'Banco',
   'plural' => 'Bancos',

   'supports' => [
      'title',
      'author',
      'custom-fields',
   ],

   /*
    * Banco utilizará también la taxonomía financiera
    * cuando lo habilitemos.
    */
   'taxonomies' => [
      'sgf_igt',
   ],

   'resources' => [
      /*
       * Se agregarán conforme avancemos.
       *
       * 'meta' =>
       *    'banco/config/meta.php',
       *
       * 'metaboxes' =>
       *    'banco/config/metaboxes.php',
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

      'menu_icon' => 'dashicons-bank',

      'rewrite' => [
         'slug' => 'bancos',
         'with_front' => false,
      ],
   ],
];