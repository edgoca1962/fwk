<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'enabled' => true,

   'singular' => 'Libro',
   'plural' => 'Libros',

   'supports' => [
      'title',
      'author',
      'custom-fields',
   ],

   /*
    * Libro utiliza la taxonomía global del módulo SGF.
    */
   'taxonomies' => [
      'sgf_igt',
   ],

   /*
    * Los recursos particulares de Libro irán creciendo aquí.
    */
   'resources' => [
      'meta' => 'libro/config/meta.php',
      // 'metaboxes' => 'libro/config/metaboxes.php',
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

      'menu_icon' => 'dashicons-book-alt',

      'rewrite' => [
         'slug' => 'libros',
         'with_front' => false,
      ],
   ],
];