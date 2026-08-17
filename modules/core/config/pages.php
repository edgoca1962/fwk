<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'login' => [
      't_main' =>
         'modules/core/view/login',

      'paginacion' => false,

      'show_content' => false,
   ],
   'solicitar-ingreso' => [
      't_main' =>
         'modules/core/view/solicitar-ingreso',

      'paginacion' => false,

      'show_content' => false,
   ],
   'usuarios' => [
      't_main' =>
         'modules/core/view/usuarios',

      'paginacion' => false,

      'show_content' => false,
   ],
];