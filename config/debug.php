<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   /*
    * Habilita el Inspector.
    *
    * Además de este valor, WP_DEBUG debe estar activo
    * y el usuario debe tener la capacidad manage_options.
    */
   'enabled' => true,

   /*
    * Capacidad necesaria para ver el Inspector.
    */
   'capability' => 'manage_options',

   /*
    * Secciones visibles.
    */
   'sections' => [
      'core' => true,
      'modules' => true,

      'post_types' => true,
      'taxonomies' => true,
      'metadata' => true,
      'metaboxes' => true,

      'request' => true,
      'ownership' => true,

      'view' => true,
      'view_history' => true,
   ],

   /*
    * Posición inicial del panel.
    *
    * Valores admitidos:
    * bottom
    * top
    */
   'position' => 'bottom',

   /*
    * El panel inicia abierto o cerrado.
    */
   'expanded' => false,
];