<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   '_debe' => [
      'type' => 'number',
      'label' => 'Debe',
      'description' =>
         'Monto registrado en el debe del movimiento.',
      'single' => true,
      'default' => 0,
      'show_in_rest' => true,
   ],

   '_haber' => [
      'type' => 'number',
      'label' => 'Haber',
      'description' =>
         'Monto registrado en el haber del movimiento.',
      'single' => true,
      'default' => 0,
      'show_in_rest' => true,
   ],

   '_monto' => [
      'type' => 'number',
      'label' => 'Monto',
      'description' =>
         'Monto del movimiento.',
      'single' => true,
      'default' => 0,
      'show_in_rest' => true,
   ],

   '_referencia' => [
      'type' => 'string',
      'label' => 'Referencia',
      'description' =>
         'Referencia asociada al movimiento.',
      'single' => true,
      'default' => '',
      'show_in_rest' => true,
      'sanitize_callback' =>
         'sanitize_text_field',
   ],
];