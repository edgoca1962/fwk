<?php

declare(strict_types=1);

namespace FWK\Modules\Core;

use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Context\ViewContext;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\View\ViewResolver;
use FWK\Modules\Core\Manifest\ModuleManifest;


if (!defined('ABSPATH')) {
   exit;
}

/**
 * Implementación base de los módulos de WP FRW.
 *
 * Proporciona:
 *
 * - Inicialización idempotente.
 * - Resolución básica por módulo, página o post type.
 * - Implementaciones predeterminadas.
 * - Carga validada de configuraciones.
 *
 * @package FWK
 */
abstract class AbstractModule implements ModuleInterface
{
   /**
    * Indica si el módulo ya fue inicializado.
    */
   private bool $booted = false;

   /**
    * Resolver visual compartido por el módulo.
    */
   private ?ViewResolver $viewResolver = null;
   /**
    * Manifiesto cargado del módulo.
    */
   private ?ModuleManifest $manifest = null;

   /**
    * Devuelve el identificador único declarado
    * en el manifiesto del módulo.
    */
   final public function get_slug(): string
   {
      return $this->manifest()->get_slug();
   }

   /**
    * Nombre descriptivo predeterminado.
    */
   public function get_name(): string
   {
      return $this->manifest()->get_name();
   }
   /**
    * Post types administrados por el módulo.
    *
    * @return string[]
    */
   public function get_post_types(): array
   {
      return array_keys(
         $this->manifest()->get_post_types()
      );
   }
   /**
    * Páginas administradas por el módulo.
    *
    * @return string[]
    */
   public function get_pages(): array
   {
      return array_keys(
         $this->manifest()->get_pages()
      );
   }
   /**
    * Inicializa el módulo una única vez.
    */
   final public function boot(): void
   {
      if ($this->booted) {
         return;
      }

      $this->register();

      $this->booted = true;

      do_action(
         'fwk_module_booted_' . sanitize_key($this->get_slug()),
         $this
      );

      do_action(
         'fwk_module_booted',
         $this
      );
   }

   /**
    * Registra hooks, servicios y componentes del módulo.
    */
   protected function register(): void
   {
   }

   /**
    * Determina si el módulo puede atender la solicitud.
    */
   public function supports(
      RequestContext $request
   ): bool {
      if (
         $request->get_requested_module()
         === $this->get_slug()
      ) {
         return true;
      }

      if (
         $request->get_page_slug() !== ''
         && in_array(
            $request->get_page_slug(),
            $this->get_pages(),
            true
         )
      ) {
         return true;
      }

      if (
         $request->get_post_type() !== ''
         && in_array(
            $request->get_post_type(),
            $this->get_post_types(),
            true
         )
      ) {
         return true;
      }

      return false;
   }

   /**
    * Configuración visual predeterminada.
    *
    * Los módulos pueden sobrescribir este método.
    *
    * Aplica la configuración visual declarativa del módulo.
    *
    */
   public function configure_view(
      ViewContext $view,
      RequestContext $request
   ): void {
      $this->resolve_view_config(
         $view,
         $request
      );
   }

   /**
    * Indica si el módulo ya fue inicializado.
    */
   public function is_booted(): bool
   {
      return $this->booted;
   }

   /**
    * Carga y valida un archivo de configuración.
    *
    * @return array<string, mixed>
    */
   protected function load_config(
      string $file
   ): array {
      if (!is_readable($file)) {
         return [];
      }

      $config = require $file;

      if (!is_array($config)) {
         throw new \UnexpectedValueException(
            sprintf(
               'El archivo de configuración "%s" debe devolver un arreglo.',
               $file
            )
         );
      }

      return $config;
   }
   /**
    * Devuelve la configuración visual del módulo.
    *
    * Los módulos pueden sobrescribir este método si la configuración
    * se encuentra en otra ubicación.
    *
    * @return array<string, mixed>
    */
   protected function get_view_config(): array
   {
      return $this->manifest()->get_views();
   }
   /**
    * Aplica la configuración declarativa del módulo.
    */
   protected function resolve_view_config(
      ViewContext $view,
      RequestContext $request
   ): void {
      $config = $this->get_view_config();

      if ($config === []) {
         return;
      }

      $this->view_resolver()->resolve(
         $view,
         $request,
         $config,
         'module:' . $this->get_slug()
      );
   }

   /**
    * Devuelve el resolver de vistas.
    */
   private function view_resolver(): ViewResolver
   {
      if (!$this->viewResolver instanceof ViewResolver) {
         $this->viewResolver = new ViewResolver();
      }

      return $this->viewResolver;
   }
   /**
    * Devuelve el manifiesto del módulo.
    */
   final public function manifest(): ModuleManifest
   {
      if ($this->manifest instanceof ModuleManifest) {
         return $this->manifest;
      }

      $directory = $this->get_module_directory();
      $file = $directory . '/config/manifest.php';

      if (!is_readable($file)) {
         throw new \RuntimeException(
            sprintf(
               'El módulo "%s" no tiene un archivo config/manifest.php.',
               static::class
            )
         );
      }

      $config = require $file;

      if (!is_array($config)) {
         throw new \UnexpectedValueException(
            sprintf(
               'El manifiesto "%s" debe devolver un arreglo.',
               $file
            )
         );
      }

      $this->manifest = new ModuleManifest(
         $directory,
         $config
      );

      return $this->manifest;
   }
   /**
    * Devuelve el directorio físico del módulo concreto.
    */
   final protected function get_module_directory(): string
   {
      $reflection = new \ReflectionClass($this);
      $file = $reflection->getFileName();

      if ($file === false) {
         throw new \RuntimeException(
            sprintf(
               'No fue posible identificar el archivo de la clase %s.',
               static::class
            )
         );
      }

      return dirname(
         wp_normalize_path($file)
      );
   }
}