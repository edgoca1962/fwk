<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Registry;

use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Support\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Registro central de módulos de WP FRW.
 *
 * Sus responsabilidades son:
 *
 * - Registrar módulos.
 * - Evitar módulos duplicados.
 * - Inicializar cada módulo una sola vez.
 * - Buscar módulos por slug.
 * - Buscar módulos por post type.
 * - Buscar módulos por página.
 *
 * @package FWK
 */
final class ModuleRegistry
{
   use Singleton;

   /**
    * Módulos registrados, indexados por slug.
    *
    * @var array<string, ModuleInterface>
    */
   private array $modules = [];

   /**
    * Indica si los módulos ya fueron inicializados.
    */
   private bool $booted = false;

   /**
    * Errores que impiden inicializar los módulos.
    *
    * @var string[]
    */
   private array $validationErrors = [];

   /**
    * Advertencias que no impiden la inicialización.
    *
    * @var string[]
    */
   private array $validationWarnings = [];

   /**
    * Orden efectivo de inicialización.
    *
    * @var string[]
    */
   private array $bootOrder = [];

   /**
    * El Registry solo puede crearse mediante get_instance().
    */
   protected function __construct()
   {
   }

   /**
    * Registra un módulo.
    *
    * @throws \InvalidArgumentException Cuando el slug está vacío.
    * @throws \LogicException Cuando otro módulo utiliza el mismo slug.
    */
   public function register(ModuleInterface $module): self
   {
      $slug = sanitize_key($module->get_slug());

      if ($slug === '') {
         throw new \InvalidArgumentException(
            sprintf(
               'El módulo %s debe declarar un slug válido.',
               get_class($module)
            )
         );
      }

      if (isset($this->modules[$slug])) {
         if ($this->modules[$slug] === $module) {
            return $this;
         }

         throw new \LogicException(
            sprintf(
               'El slug de módulo "%s" ya está registrado por %s.',
               $slug,
               get_class($this->modules[$slug])
            )
         );
      }

      $this->validate_post_types($module);
      $this->modules[$slug] = $module;

      /**
       * Permite reaccionar inmediatamente después del registro.
       */
      do_action(
         'fwk_module_registered',
         $module,
         $this
      );

      do_action(
         'fwk_module_registered_' . $slug,
         $module,
         $this
      );

      return $this;
   }

   /**
    * Registra varios módulos.
    *
    * @param ModuleInterface[] $modules
    */
   public function register_many(array $modules): self
   {
      foreach ($modules as $module) {
         if (!$module instanceof ModuleInterface) {
            throw new \InvalidArgumentException(
               'Todos los elementos deben implementar ModuleInterface.'
            );
         }

         $this->register($module);
      }

      return $this;
   }

   /**
    * Valida e inicializa todos los módulos registrados.
    */
   public function boot(): void
   {
      if ($this->booted) {
         return;
      }

      $this->validate_modules();

      /*
       * Los errores obligatorios impiden el arranque.
       */
      if ($this->validationErrors !== []) {
         $this->report_validation_errors();

         return;
      }

      try {
         $orderedModules = $this->resolve_boot_order();
      } catch (\LogicException $exception) {
         $this->validationErrors[] = $exception->getMessage();

         $this->validationErrors = array_values(
            array_unique($this->validationErrors)
         );

         $this->report_validation_errors();

         return;
      }

      $postTypeRegistry = PostTypeRegistry::get_instance();
      $taxonomyRegistry = TaxonomyRegistry::get_instance();
      $metaRegistry = MetaRegistry::get_instance();
      $metaBoxRegistry = MetaBoxRegistry::get_instance();

      do_action(
         'fwk_before_modules_boot',
         $this,
         $orderedModules
      );

      foreach ($orderedModules as $module) {
         /*
          * Cada módulo entrega sus definiciones declarativas
          * al Registry correspondiente.
          */
         $postTypeRegistry->register_module($module);
         $taxonomyRegistry->register_module($module);
         $metaRegistry->register_module($module);
         $metaBoxRegistry->register_module($module);

         /*
          * Después inicializamos los servicios particulares
          * del módulo.
          */
         $module->boot();

         $this->bootOrder[] = $module->get_slug();
      }

      /*
       * PostTypeRegistry se conecta al hook init.
       */
      $postTypeRegistry->boot();
      $taxonomyRegistry->boot();
      $metaRegistry->boot();
      $metaBoxRegistry->boot();

      $this->booted = true;

      do_action(
         'fwk_after_modules_boot',
         $this,
         $orderedModules
      );
   }
   /**
    * Indica si existe un módulo.
    */
   public function has(string $slug): bool
   {
      return isset(
         $this->modules[sanitize_key($slug)]
      );
   }

   /**
    * Devuelve un módulo por su slug.
    */
   public function get(string $slug): ?ModuleInterface
   {
      $slug = sanitize_key($slug);

      return $this->modules[$slug] ?? null;
   }

   /**
    * Devuelve todos los módulos registrados.
    *
    * @return array<string, ModuleInterface>
    */
   public function all(): array
   {
      return $this->modules;
   }

   /**
    * Devuelve todos los slugs registrados.
    *
    * @return string[]
    */
   public function slugs(): array
   {
      return array_keys($this->modules);
   }

   /**
    * Busca el módulo responsable de un post type.
    */
   public function find_by_post_type(
      string $postType
   ): ?ModuleInterface {
      $postType = sanitize_key($postType);

      if ($postType === '') {
         return null;
      }

      foreach ($this->modules as $module) {
         if (
            in_array(
               $postType,
               $module->get_post_types(),
               true
            )
         ) {
            return $module;
         }
      }

      return null;
   }

   /**
    * Busca el módulo responsable de una página.
    */
   public function find_by_page(
      string $pageSlug
   ): ?ModuleInterface {
      $pageSlug = sanitize_title($pageSlug);

      if ($pageSlug === '') {
         return null;
      }

      foreach ($this->modules as $module) {
         if (
            in_array(
               $pageSlug,
               $module->get_pages(),
               true
            )
         ) {
            return $module;
         }
      }

      return null;
   }

   /**
    * Valida dependencias obligatorias, opcionales y conflictos.
    */
   private function validate_modules(): void
   {
      $this->validationErrors = [];
      $this->validationWarnings = [];
      $this->bootOrder = [];

      foreach ($this->modules as $slug => $module) {
         $manifest = $module->manifest();

         /*
          * Dependencias obligatorias.
          */
         foreach ($manifest->get_dependencies() as $dependency) {
            if ($dependency === $slug) {
               $this->validationErrors[] = sprintf(
                  'El módulo "%s" no puede depender de sí mismo.',
                  $slug
               );

               continue;
            }

            if (!$this->has($dependency)) {
               $this->validationErrors[] = sprintf(
                  'El módulo "%s" requiere el módulo "%s", pero no está registrado.',
                  $slug,
                  $dependency
               );
            }
         }

         /*
          * Dependencias opcionales.
          */
         foreach (
            $manifest->get_optional_dependencies()
            as $optionalDependency
         ) {
            if ($optionalDependency === $slug) {
               $this->validationWarnings[] = sprintf(
                  'El módulo "%s" se declaró a sí mismo como dependencia opcional.',
                  $slug
               );

               continue;
            }

            if (!$this->has($optionalDependency)) {
               $this->validationWarnings[] = sprintf(
                  'El módulo "%s" puede utilizar "%s", pero ese módulo no está disponible.',
                  $slug,
                  $optionalDependency
               );
            }
         }

         /*
          * Conflictos.
          */
         foreach ($manifest->get_conflicts() as $conflict) {
            if ($conflict === $slug) {
               $this->validationErrors[] = sprintf(
                  'El módulo "%s" no puede declararse incompatible consigo mismo.',
                  $slug
               );

               continue;
            }

            if ($this->has($conflict)) {
               $this->validationErrors[] = sprintf(
                  'Los módulos "%s" y "%s" no pueden estar activos simultáneamente.',
                  $slug,
                  $conflict
               );
            }
         }
      }

      $this->validationErrors = array_values(
         array_unique($this->validationErrors)
      );

      $this->validationWarnings = array_values(
         array_unique($this->validationWarnings)
      );
   }
   /**
    * Valida que los post types no estén asignados a varios módulos.
    */
   private function validate_post_types(
      ModuleInterface $newModule
   ): void {
      foreach ($newModule->get_post_types() as $postType) {
         $postType = sanitize_key($postType);

         foreach ($this->modules as $registeredModule) {
            if (
               in_array(
                  $postType,
                  $registeredModule->get_post_types(),
                  true
               )
            ) {
               throw new \LogicException(
                  sprintf(
                     'El post type "%s" está asignado a los módulos "%s" y "%s".',
                     $postType,
                     $registeredModule->get_slug(),
                     $newModule->get_slug()
                  )
               );
            }
         }
      }
   }
   /**
    * Busca el primer módulo que declare soporte para la solicitud.
    */
   public function resolve(
      RequestContext $request
   ): ?ModuleInterface {
      foreach ($this->modules as $module) {
         if ($module->supports($request)) {
            return $module;
         }
      }

      return null;
   }

   /**
    * Ordena los módulos respetando sus dependencias.
    *
    * @return ModuleInterface[]
    */
   private function resolve_boot_order(): array
   {
      $ordered = [];
      $visited = [];
      $visiting = [];

      foreach (array_keys($this->modules) as $slug) {
         $this->visit_module(
            $slug,
            $ordered,
            $visited,
            $visiting
         );
      }

      return $ordered;
   }
   /**
    * Visita un módulo y sus dependencias.
    *
    * @param ModuleInterface[]  $ordered
    * @param array<string,bool> $visited
    * @param array<string,bool> $visiting
    */
   private function visit_module(
      string $slug,
      array &$ordered,
      array &$visited,
      array &$visiting
   ): void {
      if (isset($visited[$slug])) {
         return;
      }

      /*
       * Si ya estaba en proceso de visita, existe un ciclo.
       */
      if (isset($visiting[$slug])) {
         throw new \LogicException(
            sprintf(
               'Se detectó una dependencia circular relacionada con el módulo "%s".',
               $slug
            )
         );
      }

      $module = $this->get($slug);

      if (!$module instanceof ModuleInterface) {
         return;
      }

      $visiting[$slug] = true;

      foreach (
         $module->manifest()->get_dependencies()
         as $dependency
      ) {
         /*
          * Las dependencias ausentes ya fueron detectadas
          * durante validate_modules().
          */
         if (!$this->has($dependency)) {
            continue;
         }

         $this->visit_module(
            $dependency,
            $ordered,
            $visited,
            $visiting
         );
      }

      unset($visiting[$slug]);

      $visited[$slug] = true;
      $ordered[] = $module;
   }

   /**
    * Reporta los errores de validación.
    */
   private function report_validation_errors(): void
   {
      foreach ($this->validationErrors as $error) {
         _doing_it_wrong(
            __METHOD__,
            esc_html($error),
            '1.0.0'
         );
      }

      do_action(
         'fwk_module_validation_failed',
         $this->validationErrors,
         $this
      );
   }

   /**
    * Indica si el Registry terminó de arrancar.
    */
   public function is_booted(): bool
   {
      return $this->booted;
   }

   /**
    * Indica si la configuración de módulos es válida.
    */
   public function is_valid(): bool
   {
      return $this->validationErrors === [];
   }

   /**
    * Devuelve los errores de validación.
    *
    * @return string[]
    */
   public function get_validation_errors(): array
   {
      return $this->validationErrors;
   }

   /**
    * Devuelve las advertencias de validación.
    *
    * @return string[]
    */
   public function get_validation_warnings(): array
   {
      return $this->validationWarnings;
   }

   /**
    * Devuelve el orden efectivo de arranque.
    *
    * @return string[]
    */
   public function get_boot_order(): array
   {
      return $this->bootOrder;
   }
}