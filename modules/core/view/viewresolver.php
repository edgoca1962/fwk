<?php

declare(strict_types=1);

namespace FWK\Modules\Core\View;

use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Context\ViewContext;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Resuelve configuraciones visuales declarativas.
 *
 * Recibe:
 *
 * - El contexto visual que se modificará.
 * - El contexto de la solicitud.
 * - La configuración visual del módulo.
 * - El identificador del origen de la configuración.
 *
 * @package FWK
 */
final class ViewResolver
{
   /**
    * Configura la vista utilizando una definición declarativa.
    *
    * @param array<string, mixed> $config
    */
   public function resolve(
      ViewContext $view,
      RequestContext $request,
      array $config,
      string $source = 'view-resolver'
   ): void {
      /*
       * 1. Configuración general del módulo.
       */
      $this->apply_section(
         $view,
         $config['defaults'] ?? [],
         $source . ':defaults'
      );

      /*
       * 2. Tipo general de solicitud.
       */
      $this->apply_request_type(
         $view,
         $request,
         $config,
         $source
      );

      /*
       * 3. Página específica.
       */
      $this->apply_page(
         $view,
         $request,
         $config,
         $source
      );

      /*
       * 4. Post type.
       */
      $this->apply_post_type(
         $view,
         $request,
         $config,
         $source
      );

      /*
       * 5. Taxonomía y término.
       */
      $this->apply_taxonomy(
         $view,
         $request,
         $config,
         $source
      );

      /*
       * 6. Reglas especiales del módulo.
       */
      $this->apply_rules(
         $view,
         $request,
         $config,
         $source
      );
   }

   /**
    * Aplica configuraciones según el tipo general de solicitud.
    *
    * @param array<string, mixed> $config
    */
   private function apply_request_type(
      ViewContext $view,
      RequestContext $request,
      array $config,
      string $source
   ): void {
      $type = $request->get_type();

      $typeMap = [
         'front_page' => 'front_page',
         'home' => 'home',
         'search' => 'search',
         '404' => '404',
         'author' => 'author',
         'archive' => 'archive',
         'post_type_archive' => 'post_type_archive',
         'taxonomy' => 'taxonomy',
         'page' => 'page',
         'singular' => 'singular',
      ];

      $section = $typeMap[$type] ?? '';

      if ($section === '') {
         return;
      }

      $this->apply_section(
         $view,
         $config[$section] ?? [],
         $source . ':' . $section
      );
   }

   /**
    * Aplica configuración específica de una página.
    *
    * @param array<string, mixed> $config
    */
   private function apply_page(
      ViewContext $view,
      RequestContext $request,
      array $config,
      string $source
   ): void {
      $pageSlug = $request->get_page_slug();

      if ($pageSlug === '') {
         return;
      }

      $pages = $config['pages'] ?? [];

      if (!is_array($pages)) {
         return;
      }

      $this->apply_section(
         $view,
         $pages[$pageSlug] ?? [],
         $source . ':page:' . $pageSlug
      );
   }

   /**
    * Aplica configuración del post type actual.
    *
    * La definición puede tener:
    *
    * - defaults
    * - archive
    * - singular
    * - search
    *
    * @param array<string, mixed> $config
    */
   private function apply_post_type(
      ViewContext $view,
      RequestContext $request,
      array $config,
      string $source
   ): void {
      $postType = $request->get_post_type();

      if ($postType === '') {
         return;
      }

      $postTypes = $config['post_types'] ?? [];

      if (
         !is_array($postTypes)
         || !isset($postTypes[$postType])
         || !is_array($postTypes[$postType])
      ) {
         return;
      }

      $definition = $postTypes[$postType];

      /*
       * Configuración general del CPT.
       */
      $this->apply_section(
         $view,
         $definition['defaults'] ?? [],
         $source . ':post-type:' . $postType . ':defaults'
      );

      /*
       * Vista singular.
       */
      if ($request->is_singular($postType)) {
         $this->apply_section(
            $view,
            $definition['singular'] ?? [],
            $source . ':post-type:' . $postType . ':singular'
         );

         return;
      }

      /*
       * Archivo específico del CPT.
       */
      if ($request->is_post_type_archive($postType)) {
         $this->apply_section(
            $view,
            $definition['archive'] ?? [],
            $source . ':post-type:' . $postType . ':archive'
         );

         return;
      }

      /*
       * Búsqueda.
       */
      if ($request->is_search()) {
         $this->apply_section(
            $view,
            $definition['search'] ?? [],
            $source . ':post-type:' . $postType . ':search'
         );

         return;
      }

      /*
       * Vista de listado o fallback del CPT.
       */
      $this->apply_section(
         $view,
         $definition['listing'] ?? [],
         $source . ':post-type:' . $postType . ':listing'
      );
   }

   /**
    * Aplica configuración de taxonomía y término.
    *
    * @param array<string, mixed> $config
    */
   private function apply_taxonomy(
      ViewContext $view,
      RequestContext $request,
      array $config,
      string $source
   ): void {
      $taxonomy = $request->get_taxonomy();

      if ($taxonomy === '') {
         return;
      }

      $taxonomies = $config['taxonomies'] ?? [];

      if (
         !is_array($taxonomies)
         || !isset($taxonomies[$taxonomy])
         || !is_array($taxonomies[$taxonomy])
      ) {
         return;
      }

      $definition = $taxonomies[$taxonomy];

      /*
       * Configuración general de la taxonomía.
       */
      $this->apply_section(
         $view,
         $definition['defaults'] ?? [],
         $source . ':taxonomy:' . $taxonomy . ':defaults'
      );

      /*
       * Configuración específica de un término.
       */
      $termSlug = $request->get_term_slug();
      $terms = $definition['terms'] ?? [];

      if (
         $termSlug !== ''
         && is_array($terms)
      ) {
         $this->apply_section(
            $view,
            $terms[$termSlug] ?? [],
            $source . ':taxonomy:' . $taxonomy . ':term:' . $termSlug
         );
      }
   }

   /**
    * Ejecuta reglas declarativas adicionales.
    *
    * @param array<string, mixed> $config
    */
   private function apply_rules(
      ViewContext $view,
      RequestContext $request,
      array $config,
      string $source
   ): void {
      $rules = $config['rules'] ?? [];

      if (!is_array($rules)) {
         return;
      }

      foreach ($rules as $index => $rule) {
         if (!is_array($rule)) {
            continue;
         }

         $when = $rule['when'] ?? [];
         $values = $rule['values'] ?? [];

         if (
            !is_array($when)
            || !is_array($values)
         ) {
            continue;
         }

         if (!$this->matches($request, $when)) {
            continue;
         }

         $this->apply_section(
            $view,
            $values,
            sprintf(
               '%s:rule:%d',
               $source,
               $index
            )
         );
      }
   }

   /**
    * Evalúa una regla declarativa.
    *
    * @param array<string, mixed> $conditions
    */
   private function matches(
      RequestContext $request,
      array $conditions
   ): bool {
      foreach ($conditions as $key => $expected) {
         switch ($key) {
            case 'request_type':
               if ($request->get_type() !== $expected) {
                  return false;
               }
               break;

            case 'post_type':
               if ($request->get_post_type() !== $expected) {
                  return false;
               }
               break;

            case 'page_slug':
               if ($request->get_page_slug() !== $expected) {
                  return false;
               }
               break;

            case 'post_slug':
               if ($request->get_post_slug() !== $expected) {
                  return false;
               }
               break;

            case 'taxonomy':
               if ($request->get_taxonomy() !== $expected) {
                  return false;
               }
               break;

            case 'term_slug':
               if ($request->get_term_slug() !== $expected) {
                  return false;
               }
               break;

            case 'requested_module':
               if ($request->get_requested_module() !== $expected) {
                  return false;
               }
               break;

            case 'logged_in':
               if (is_user_logged_in() !== (bool) $expected) {
                  return false;
               }
               break;

            default:
               /*
                * Una condición desconocida invalida la regla.
                */
               return false;
         }
      }

      return true;
   }

   /**
    * Aplica una sección de configuración al ViewContext.
    *
    * Los valores pueden ser estáticos o callables.
    *
    * @param mixed $section
    */
   private function apply_section(
      ViewContext $view,
      mixed $section,
      string $source
   ): void {
      if (is_callable($section)) {
         $section = $section(
            $view
         );
      }

      if (!is_array($section)) {
         return;
      }

      $resolved = [];

      foreach ($section as $key => $value) {
         if (!is_string($key)) {
            continue;
         }

         /*
          * Permite valores dinámicos:
          *
          * 'titulo' => fn () => get_the_title()
          */
         if (is_callable($value)) {
            $value = $value($view);
         }

         $resolved[$key] = $value;
      }

      if ($resolved === []) {
         return;
      }

      $view->merge(
         $resolved,
         $source
      );
   }
}