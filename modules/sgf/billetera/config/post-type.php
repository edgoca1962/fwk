<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   /*
    * Etiquetas humanas.
    */
   'singular' => 'Billetera',
   'plural' => 'Billeteras',

   /*
    * Características habilitadas.
    */
   'supports' => [
      'title',
      'thumbnail',
      'author',
      'custom-fields',
   ],

   /*
    * Las taxonomías se incorporarán cuando construyamos
    * TaxonomyRegistry.
    */
   'taxonomies' => [],

   /**
    * Campos personalizados asociados al CPT.
    * Metaboxes
    */
   'resources' => [
      'meta' => 'billetera/config/meta.php',
      'metaboxes' => 'billetera/config/metaboxes.php',
   ],
   /*
    * Argumentos enviados posteriormente a
    * register_post_type().
    */
   'args' => [
      'public' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_rest' => true,

      'has_archive' => true,

      'menu_icon' => 'dashicons-portfolio',

      'rewrite' => [
         'slug' => 'billeteras',
         'with_front' => false,
      ],
   ],
];