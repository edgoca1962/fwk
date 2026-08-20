<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   /*
    * Configuración general del módulo.
    */
   'defaults' => [
      'modulo' => 'post',
      'frmplaceholder' => __('Buscar artículo', 'FWK'),
      'search' => rest_url('wp/v2/posts?search='),
      'msgsearch' => __(
         'No se encontraron artículos con ese tema.',
         'FWK'
      ),
   ],

   /*
    * Página asignada al listado del blog.
    */
   'home' => [
      't_main' => 'modules/post/view/blog',
   ],

   /*
    * Páginas funcionales pertenecientes al módulo.
    */
   'pages' => [
      'blog' => [
         't_main' => 'modules/post/view/blog',
      ],

   ],

   /*
    * Post type nativo administrado por el módulo.
    */
   'post_types' => [
      'post' => [
         'listing' => [
            't_main' => 'modules/post/view/blog',
         ],

         'archive' => [
            't_main' => 'modules/post/view/blog',
         ],

         'singular' => [
            't_main' => 'modules/post/view/single',
         ],

         'search' => [
            't_main' => 'modules/post/view/blog',
         ],
      ],
   ],
];