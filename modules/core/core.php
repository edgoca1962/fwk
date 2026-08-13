<?php

declare(strict_types=1);

namespace FWK\Modules\Core;

use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Context\ViewContext;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Registry\ModuleRegistry;
use FWK\Modules\Core\Support\Singleton;
use FWK\Modules\Core\Support\WPSetup;
use FWK\Modules\Core\Debug\DebugInspector;

if (!defined('ABSPATH')) {
   exit;
}

final class Core
{
   use Singleton;

   private ModuleRegistry $modules;

   private ?RequestContext $request = null;

   private ?ViewContext $view = null;
   /**
    * Indica si el núcleo terminó su inicialización.
    */
   private bool $booted = false;

   protected function __construct()
   {
      $this->modules = ModuleRegistry::get_instance();

      $this->boot();
   }

   /**
    * Inicializa el Kernel una única vez.
    */
   private function boot(): void
   {
      if ($this->booted) {
         return;
      }

      /*
       * Servicios globales del tema.
       */
      WPSetup::get_instance();

      /*
       * Registro de módulos configurados.
       */
      $this->register_modules();

      /*
       * Inicialización de cada módulo.
       */
      $this->modules->boot();

      /*
       * Core solo se considera completamente inicializado
       * si los módulos pudieron arrancar.
       */
      $this->booted = $this->modules->is_booted();

      /*
       * El Inspector debe inicializarse incluso si existe
       * un error de validación, para poder mostrarlo.
       */
      DebugInspector::get_instance();

      if ($this->booted) {
         do_action(
            'fwk_core_booted',
            $this
         );
      }
   }
   /**
    * Indica si Core completó su inicialización.
    */
   public function is_booted(): bool
   {
      return $this->booted;
   }
   /**
    * Construye y devuelve el contexto visual de la solicitud.
    */
   public function resolve_view(): ViewContext
   {
      if ($this->view instanceof ViewContext) {
         return $this->view;
      }

      $request = $this->request();

      $view = new ViewContext(
         $this->load_view_defaults()
      );

      /*
       * Información básica de la solicitud disponible en la vista.
       */
      $view->merge([
         'request_type' => $request->get_type(),
         'postType' => $request->get_post_type(),
         'page_slug' => $request->get_page_slug(),
         'post_slug' => $request->get_post_slug(),
         'taxonomy' => $request->get_taxonomy(),
         'term_slug' => $request->get_term_slug(),
         'paged' => $request->get_paged(),
      ], 'core:request');

      $module = $this->resolve_module($request);

      if ($module instanceof ModuleInterface) {
         $view->set(
            'modulo',
            $module->get_slug(),
            'core:module'
         );

         $module->configure_view(
            $view,
            $request
         );
      } else {
         $this->configure_fallback_view(
            $view,
            $request
         );
      }

      /**
       * Permite modificar la vista una vez resuelto el módulo.
       */
      do_action(
         'fwk_view_resolved',
         $view,
         $request,
         $module
      );

      $this->view = $view->filter();

      return $this->view;
   }

   public function request(): RequestContext
   {
      if (!$this->request instanceof RequestContext) {
         $this->request = RequestContext::capture();
      }

      return $this->request;
   }

   public function modules(): ModuleRegistry
   {
      return $this->modules;
   }

   /**
    * Determina el módulo responsable de la solicitud.
    */
   private function resolve_module(
      RequestContext $request
   ): ?ModuleInterface {
      /*
       * 1. Módulo solicitado expresamente.
       */
      $requestedModule = $request->get_requested_module();

      if (
         $requestedModule !== ''
         && $this->modules->has($requestedModule)
      ) {
         return $this->modules->get($requestedModule);
      }

      /*
       * 2. Página administrada por un módulo.
       *
       * Se revisa antes del post type porque todas las páginas
       * tienen post_type "page".
       */
      $pageSlug = $request->get_page_slug();

      if ($pageSlug !== '') {
         $module = $this->modules->find_by_page(
            $pageSlug
         );

         if ($module instanceof ModuleInterface) {
            return $module;
         }
      }

      /*
       * 3. Post type administrado por un módulo.
       */
      $postType = $request->get_post_type();

      if ($postType !== '') {
         $module = $this->modules->find_by_post_type(
            $postType
         );

         if ($module instanceof ModuleInterface) {
            return $module;
         }
      }

      /*
       * 4. Resolución personalizada mediante supports().
       */
      return $this->modules->resolve($request);
   }
   /**
    * Configura la vista cuando ningún módulo atiende la solicitud.
    */
   private function configure_fallback_view(
      ViewContext $view,
      RequestContext $request
   ): void {
      if ($request->is_404()) {
         $view->merge([
            'titulo' => __('Página no encontrada', 'FWK'),
            'display' => 'display-6',
            'subtitulo' => sprintf(
               '<a href="%s" class="enlaceBlanco">%s</a>',
               esc_url(home_url('/')),
               esc_html__('Volver al inicio', 'FWK')
            ),
            'displaysub' => '',
            'height' => '100dvh',
            't_main' => '',
            't_none' => 'modules/core/view/none',
            'paginacion' => false,
         ], 'core:fallback:404');

         return;
      }

      $view->merge([
         'titulo' => get_the_archive_title(),
         't_main' => 'modules/core/view/content',
         'paginacion' => $request->is_archive(),
      ], 'core:fallback');
   }

   /**
    * Registra los módulos activos configurados.
    */
   private function register_modules(): void
   {
      foreach ($this->load_module_classes() as $moduleClass) {
         if (!is_string($moduleClass)) {
            _doing_it_wrong(
               __METHOD__,
               'Cada módulo debe declararse mediante un nombre de clase.',
               '1.0.0'
            );

            continue;
         }

         if (!class_exists($moduleClass)) {
            _doing_it_wrong(
               __METHOD__,
               sprintf(
                  'La clase del módulo "%s" no existe.',
                  esc_html($moduleClass)
               ),
               '1.0.0'
            );

            continue;
         }

         if (
            !is_subclass_of(
               $moduleClass,
               ModuleInterface::class
            )
         ) {
            _doing_it_wrong(
               __METHOD__,
               sprintf(
                  'La clase "%s" debe implementar ModuleInterface.',
                  esc_html($moduleClass)
               ),
               '1.0.0'
            );

            continue;
         }

         /*
          * ModuleInterface garantiza la existencia de get_instance().
          *
          * @var ModuleInterface $module
          */
         $module = $moduleClass::get_instance();

         $this->modules->register($module);
      }
   }
   /**
    * @return class-string[]
    */
   private function load_module_classes(): array
   {
      $file = get_template_directory()
         . '/config/modules.php';

      if (!is_readable($file)) {
         return [];
      }

      $modules = require $file;

      if (!is_array($modules)) {
         throw new \UnexpectedValueException(
            'config/modules.php debe devolver un arreglo.'
         );
      }

      $modules = apply_filters(
         'fwk_modules',
         $modules
      );

      return array_values(
         array_unique($modules)
      );
   }

   /**
    * @return array<string, mixed>
    */
   private function load_view_defaults(): array
   {
      $file = get_template_directory()
         . '/config/view.php';

      if (!is_readable($file)) {
         return [];
      }

      $defaults = require $file;

      if (!is_array($defaults)) {
         throw new \UnexpectedValueException(
            'config/view.php debe devolver un arreglo.'
         );
      }

      return $defaults;
   }
}