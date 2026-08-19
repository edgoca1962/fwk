<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [

   /*
    * Configuración general del área pública.
    */
   'public' => [
      'enabled' => true,

      /*
       * Ruta inicial para visitantes.
       */
      'home' => '/landing-page',
   ],

   /*
    * Configuración para usuarios autenticados.
    */
   'authenticated' => [

      /*
       * Ruta inicial después del login.
       */
      'home' => '/landing-page',
   ],

   /*
    * Páginas administradas por FRW Base.
    */
   'pages' => [

      /*
       * Páginas base obligatorias.
       */
      'login' => [
         'required' => true,
         'enabled' => true,
         'title' => 'Ingresar',
         'slug' => 'login',
      ],

      'request_access' => [
         'required' => true,
         'enabled' => true,
         'title' => 'Solicitar ingreso',
         'slug' => 'solicitar-ingreso',
      ],

      'users' => [
         'required' => true,
         'enabled' => true,
         'title' => 'Usuarios',
         'slug' => 'usuarios',
      ],

      /*
       * Páginas públicas opcionales.
       */
      'landing_page' => [
         'required' => false,
         'enabled' => true,
         'title' => 'Inicio',
         'slug' => 'landing-page',
      ],

      'blog' => [
         'required' => false,
         'enabled' => true,
         'title' => 'Blog',
         'slug' => 'blog',
      ],

      'about' => [
         'required' => false,
         'enabled' => true,
         'title' => 'Nosotros',
         'slug' => 'nosotros',
      ],
   ],

   /*
    * Menús que FRW podrá provisionar.
    */
   'menus' => [

      'public' => [
         'enabled' => true,
         'name' => 'Menú público',
         'location' => 'publico',

         /*
          * Las claves corresponden a
          * páginas declaradas arriba.
          */
         'items' => [
            'landing_page',
            'blog',
            'about',
            'login',
         ],
      ],

      'principal' => [
         'enabled' => true,
         'name' => 'Menú principal',
         'location' => 'principal',

         'items' => [
            'landing_page',
            'blog',
         ],
      ],
   ],
];