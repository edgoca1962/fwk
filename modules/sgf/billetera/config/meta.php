<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   '_moneda' => [
      'type' => 'integer',

      'label' => 'Moneda',

      'description' =>
         'Identificador de la moneda de la billetera.',

      'single' => true,

      'default' => 1,

      'show_in_rest' => true,

      'sanitize_callback' =>
         'absint',
   ],
   '_saldo' => [
      'type' => 'number',

      'label' => 'Saldo actual',

      'description' =>
         'Saldo actual calculado de la billetera.',

      'single' => true,

      'default' => 0,

      'show_in_rest' => true,
   ],
];