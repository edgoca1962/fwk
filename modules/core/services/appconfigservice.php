<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Provee acceso centralizado a la
 * configuración global de la aplicación.
 */
final class AppConfigService
{
   /**
    * Configuración global.
    *
    * @var array<string, mixed>
    */
   private array $config = [];

   public function __construct()
   {
      $configFile =
         get_template_directory()
         . '/config/app.php';

      if (!is_readable($configFile)) {
         return;
      }

      $config =
         require $configFile;

      if (!is_array($config)) {
         return;
      }

      $this->config =
         $config;
   }

   /**
    * Devuelve toda la configuración.
    *
    * @return array<string, mixed>
    */
   public function all(): array
   {
      return $this->config;
   }
}