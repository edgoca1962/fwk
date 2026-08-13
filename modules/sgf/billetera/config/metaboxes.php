<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   'billetera-datos' => [
      'title' => 'Datos de la billetera',

      'post_types' => [
         'billetera',
      ],

      'context' => 'normal',

      'priority' => 'default',

      'fields' => [
         '_moneda' => [
            'type' => 'select',

            'label' => 'Moneda',

            'description' =>
               'Seleccione la moneda de la billetera.',

            'options' => [
               1 => 'Moneda local',
               2 => 'Moneda extranjera',
            ],
         ],
         '_saldo' => [
            'type' => 'number',

            'label' => 'Saldo actual',

            'description' =>
               'Permite ajustar manualmente el saldo de la billetera desde el área administrativa.',
         ],
      ],
   ],
];