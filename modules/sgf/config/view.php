<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   /*
    * Valores comunes a todas las vistas de SGF.
    */
   'defaults' => [
      'modulo' => 'sgf',
      't_asideR' => 'modules/sgf/view/asideright',
   ],

   /** 
    * Páginas funcionales.
    */
   'pages' => [
      'tablero' => [
         'titulo' => __('Tablero financiero', 'FWK'),
         'main' => 'col-12',
         'asideR' => '',
         't_asideR' => '',
         't_main' => 'modules/sgf/view/tablero',
         'paginacion' => false,
      ],

      'cambiar_cat_masivo' => [
         'titulo' => __('Categorizar Movimientos', 'FWK'),
         'main' => 'col-12',
         'asideR' => '',
         't_asideR' => '',
         't_main' => 'modules/sgf/view/cambiar_cat_masivo',
         'paginacion' => false,
      ],

   ],

   /*
    * Post types administrados por SGF.
    */
   'post_types' => [
      'libro' => [
         'defaults' => [
            'titulo' => __('Movimientos Contabilizados', 'FWK'),
         ],

         'listing' => [
            'article' => 'row row-cols-1 g-3',
            't_main' => 'modules/sgf/libro/view/libro',
            'paginacion' => true,
         ],

         'archive' => [
            'article' => 'row row-cols-1 g-3',
            't_main' => 'modules/sgf/libro/view/libro',
            'paginacion' => true,
         ],

         'singular' => [
            'titulo' => static fn(): string => get_the_title(),
            'article' => '',
            't_main' => 'modules/sgf/libro/view/single',
            'paginacion' => false,
         ],
      ],

      'banco' => [
         'defaults' => [
            'titulo' => __('Movimientos Bancarios', 'FWK'),
         ],

         'listing' => [
            'article' => 'row row-cols-2 row-cols-md-4 g-3',
            't_main' => 'modules/sgf/banco/view/banco',
            'paginacion' => true,
         ],

         'archive' => [
            'article' => 'row row-cols-2 row-cols-md-4 g-3',
            't_main' => 'modules/sgf/banco/view/banco',
            'paginacion' => true,
         ],

         'singular' => [
            'titulo' => static fn(): string => get_the_title(),
            'article' => '',
            't_main' => 'modules/sgf/banco/view/single',
            'paginacion' => false,
         ],
      ],

      'billetera' => [
         'defaults' => [
            'titulo' => __('Billetera', 'FWK'),
         ],

         'listing' => [
            'article' => 'row row-cols-1 row-cols-md-2 g-3',
            't_main' => 'modules/sgf/billetera/view/billetera',
            'paginacion' => true,
         ],

         'archive' => [
            'article' => 'row row-cols-1 row-cols-md-2 g-3',
            't_main' => 'modules/sgf/billetera/view/billetera',
            'paginacion' => true,
         ],

         'singular' => [
            'titulo' => static fn(): string => get_the_title(),
            'article' => '',
            't_main' => 'modules/sgf/billetera/view/single',
            'paginacion' => false,
         ],
      ],

      'presupuesto' => [
         'defaults' => [
            'titulo' => __('Presupuestos', 'FWK'),
         ],

         'listing' => [
            'article' => 'row row-cols-1 g-3',
            't_main' => 'modules/sgf/presupuesto/view/presupuesto',
            'paginacion' => true,
         ],

         'archive' => [
            'article' => 'row row-cols-1 g-3',
            't_main' => 'modules/sgf/presupuesto/view/presupuesto',
            'paginacion' => true,
         ],

         'singular' => [
            'titulo' => static fn(): string => get_the_title(),
            'article' => '',
            't_main' => 'modules/sgf/presupuesto/view/single',
            'paginacion' => false,
         ],
      ],
   ],

   /*
    * Ejemplo de regla adicional.
    */
   'rules' => [
      [
         'when' => [
            'page_slug' => 'tablero',
            'logged_in' => false,
         ],

         'values' => [
            'titulo' => __('Favor ingresar a la aplicación', 'FWK'),
            't_main' => 'modules/core/view/none',
            't_asideR' => '',
            'asideR' => '',
            'paginacion' => false,
         ],
      ],
   ],
];