<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'slug' => 'post',

   'name' => 'Blog',

   'description' =>
      'Módulo para la administración del blog nativo de WordPress.',

   'version' => '1.0.0',

   'dependencies' => [],

   'optional_dependencies' => [],

   'conflicts' => [],

   'resources' => [
      /*
       * Páginas administradas por el módulo.
       *
       * En una etapa posterior PageRegistry podrá utilizar
       * esta información para crearlas o validarlas.
       */
      'pages' => [
         'blog' => [
            'title' => 'Blog',
         ],

         'principal' => [
            'title' => 'Principal',
         ],
      ],

      /*
       * El post type "post" es nativo de WordPress.
       *
       * La propiedad native evitará que PostTypeRegistry
       * intente registrarlo nuevamente.
       */
      'post_types' => [
         'post' =>
            'config/post-type.php',
      ],

      'taxonomies' => [
         'category' => [
            'native' => true,
         ],

         'post_tag' => [
            'native' => true,
         ],
      ],

      'views' => 'config/view.php',

      'roles' => [],
      'services' => [],
      'assets' => [],
      'ajax' => [],
      'rest' => [],
   ],
];
