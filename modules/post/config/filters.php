<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [

   /*
    * Búsqueda libre por texto.
    */
   'search' => [
      'enabled' =>
         true,

      'param' =>
         'buscar',

      'type' =>
         'text',

      'label' =>
         __(
            'Buscar',
            'FWK'
         ),

      'placeholder' =>
         __(
            'Buscar artículo',
            'FWK'
         ),
   ],

   /*
    * Filtros por taxonomía.
    */
   'taxonomies' => [

      'category' => [
         'enabled' =>
            true,

         'param' =>
            'categoria',

         'label' =>
            __(
               'Categoría',
               'FWK'
            ),
      ],

      'post_tag' => [
         'enabled' =>
            true,

         'param' =>
            'tag',

         'label' =>
            __(
               'Etiqueta',
               'FWK'
            ),
      ],

   ],

   /*
    * Orden del listado.
    */
   'order' => [
      'enabled' =>
         true,

      'param' =>
         'orden',

      'default' =>
         'DESC',

      'allowed' => [
         'DESC',
         'ASC',
      ],
   ],

   /*
    * Paginación.
    */
   'pagination' => [
      'enabled' =>
         true,

      'param' =>
         'paged',

      'default' =>
         1,
   ],
];