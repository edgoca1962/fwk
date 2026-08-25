<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   /*
    * El post type "post" es nativo de WordPress.
    *
    * PostTypeRegistry debe reconocerlo,
    * pero no registrarlo nuevamente.
    */
   'native' => true,

   /*
    * Recursos propios del Post Type.
    */
   'resources' => [
      'filters' =>
         'config/filters.php',
   ],
];