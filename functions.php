<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

spl_autoload_register(
   static function (string $class): void {
      $prefix = 'FWK\\';

      if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
         return;
      }

      $relativeClass = substr(
         $class,
         strlen($prefix)
      );

      if (
         $relativeClass === false
         || $relativeClass === ''
      ) {
         return;
      }

      $relativePath = str_replace(
         '\\',
         DIRECTORY_SEPARATOR,
         $relativeClass
      );

      $file = strtolower(
         __DIR__
         . DIRECTORY_SEPARATOR
         . $relativePath
         . '.php'
      );

      if (is_readable($file)) {
         require_once $file;
      }
   }
);

use FWK\Modules\Core\Core;

if (!function_exists('FWK_get_theme_instance')) {
   /**
    * Inicializa y devuelve la instancia principal de WP FRW.
    */
   function FWK_get_theme_instance(): Core
   {
      return Core::get_instance();
   }
}

FWK_get_theme_instance();