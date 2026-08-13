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
      'asideL' => '',
      'asideR' => 'col-md-3',
      't_asideL' => '',
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
      'titulo' => __('Blog', 'FWK'),
      'article' => 'row row-cols-2 row-cols-md-3 row-cols-xxl-4 g-3 mb-3',
      't_main' => 'modules/post/view/post',
      'paginacion' => true,
   ],

   /*
    * Páginas funcionales pertenecientes al módulo.
    */
   'pages' => [
      'blog' => [
         'titulo' => __('Blog', 'FWK'),
         'article' => 'row row-cols-2 row-cols-md-3 row-cols-xxl-4 g-3 mb-3',
         't_main' => 'modules/post/view/post',
         'paginacion' => true,
      ],

      'principal' => [
         'header' => '',
         'div1' => '',
         'div2' => '',
         'asideL' => '',
         'asideR' => '',
         'main' => '',
         'article' => '',
         'pagination' => '',
         'paginacion' => false,
         't_banner' => '',
         't_asideL' => '',
         't_main' => 'modules/post/view/principal',
         't_asideR' => '',
         't_footer' => '',
         'titulo' => '',
         'show_content' => false,
      ],
   ],

   /*
    * Post type nativo administrado por el módulo.
    */
   'post_types' => [
      'post' => [
         'defaults' => [
            'titulo' => __('Blog', 'FWK'),
            'article' => 'row row-cols-2 row-cols-md-3 row-cols-xxl-4 g-3 mb-3',
         ],

         'listing' => [
            't_main' => 'modules/post/view/post',
            'paginacion' => true,
         ],

         'archive' => [
            't_main' => 'modules/post/view/post',
            'paginacion' => true,
         ],

         'singular' => [
            'titulo' => static fn(): string => get_the_title(),
            'article' => '',
            't_main' => 'modules/post/view/single',
            'paginacion' => false,
            'show_content' => false,
         ],

         'search' => [
            'titulo' => static fn(): string => sprintf(
               __('Resultados para: %s', 'FWK'),
               get_search_query()
            ),
            't_main' => 'modules/post/view/post',
         ],
      ],
   ],
];