<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Contracts;

use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Context\ViewContext;
use FWK\Modules\Core\Manifest\ModuleManifest;

if (!defined('ABSPATH')) {
   exit;
}

interface ModuleInterface
{
   /**
    * Devuelve la instancia única del módulo.
    *
    * @return static
    */
   public static function get_instance(): static;
   /**
    * Devuelve el manifiesto del módulo.
    */
   public function manifest(): ModuleManifest;

   public function get_slug(): string;

   public function get_name(): string;

   /**
    * @return string[]
    */
   public function get_post_types(): array;

   /**
    * @return string[]
    */
   public function get_pages(): array;

   public function boot(): void;

   public function supports(
      RequestContext $request
   ): bool;

   public function configure_view(
      ViewContext $view,
      RequestContext $request
   ): void;
}