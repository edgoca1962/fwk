<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'slug' => 'sgf',

   'name' =>
      'Sistema de Gestión Financiera',

   'description' =>
      'Módulo para la administración de billeteras, movimientos contabilizados, movimientos bancarios y presupuestos.',

   'version' => '1.0.0',

   'dependencies' => [],

   'optional_dependencies' => [],

   'conflicts' => [],

   'resources' => [
      /*
       * Páginas funcionales y analíticas.
       */
      'pages' => [
         'tablero' => [
            'title' => 'Tablero financiero',
         ],

         'cambiar_cat_masivo' => [
            'title' =>
               'Cambiar categorías masivamente',
         ],
      ],

      /*
       * Por ahora solamente declaramos pertenencia.
       *
       * En la Capa 3 completaremos sus argumentos de registro.
       */
      'post_types' => [
         'billetera' => 'billetera/config/post-type.php',
         'libro' => 'libro/config/post-type.php',
         'banco' => 'banco/config/post-type.php',
         'presupuesto' => 'presupuesto/config/post-type.php',
      ],

      /*
       * Se completará con TaxonomyRegistry.
       */
      'taxonomies' => [
         'sgf_igt' => [
            'singular' => 'Rubro',
            'plural' => 'Rubros',

            /*
             * SGF utiliza una jerarquía de términos.
             */
            'hierarchical' => true,

            /*
             * Por ahora la asociamos con libro, banco
             * y presupuesto.
             *
             * Podemos ajustar estos object types cuando
             * terminemos de definir el modelo funcional.
             */
            'object_types' => [
               'libro',
            ],

            'args' => [
               'public' => true,
               'show_ui' => true,
               'show_admin_column' => true,
               'show_in_rest' => true,

               'rewrite' => [
                  'slug' => 'sgf_igt',
                  'with_front' => false,
               ],
            ],
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