<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Provisiona la estructura base de la aplicación
 * a partir de la configuración global.
 */
final class ProvisioningService
{
   /**
    * Configuración global de la aplicación.
    *
    * @var array<string, mixed>
    */
   private array $config = [];

   public function __construct()
   {
      $appConfig =
         new AppConfigService();

      $this->config =
         $appConfig->all();
   }

   /**
    * Devuelve la configuración global
    * de la aplicación.
    *
    * @return array<string, mixed>
    */
   public function get_config(): array
   {
      return $this->config;
   }
   /**
    * Provisiona las páginas declaradas
    * en la configuración global.
    */
   public function provision_pages(): void
   {
      $pages =
         $this->config['pages']
         ?? [];

      if (!is_array($pages)) {
         return;
      }

      foreach ($pages as $pageConfig) {

         if (!is_array($pageConfig)) {
            continue;
         }

         $required = (bool) (
            $pageConfig['required']
            ?? false
         );

         $enabled = (bool) (
            $pageConfig['enabled']
            ?? false
         );

         if (
            !$required
            && !$enabled
         ) {
            continue;
         }

         $title = sanitize_text_field(
            (string) (
               $pageConfig['title']
               ?? ''
            )
         );

         $slug = sanitize_title(
            (string) (
               $pageConfig['slug']
               ?? ''
            )
         );

         if (
            $title === ''
            || $slug === ''
         ) {
            continue;
         }

         /*
          * Si ya existe una página con ese slug,
          * no hacemos nada.
          */
         $existingPage =
            get_page_by_path(
               $slug,
               OBJECT,
               'page'
            );

         if ($existingPage instanceof \WP_Post) {
            continue;
         }

         wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => '',
         ]);
      }
   }

   /**
    * Provisiona el menú público declarado
    * en la configuración global.
    *
    * Reglas:
    * - Si la ubicación ya tiene un menú asignado,
    *   se respeta completamente.
    * - Si no hay menú asignado pero existe uno
    *   con el nombre configurado, se reutiliza
    *   sin modificar sus elementos.
    * - Solo cuando el menú no existe se crea
    *   y se agregan los elementos iniciales.
    */
   public function provision_public_menu(): void
   {
      $menuConfig =
         $this->config['menus']['public']
         ?? [];

      if (!is_array($menuConfig)) {
         return;
      }

      $enabled = (bool) (
         $menuConfig['enabled']
         ?? false
      );

      if (!$enabled) {
         return;
      }

      $menuName = sanitize_text_field(
         (string) (
            $menuConfig['name']
            ?? ''
         )
      );

      $location = sanitize_key(
         (string) (
            $menuConfig['location']
            ?? ''
         )
      );

      $items =
         $menuConfig['items']
         ?? [];

      if (
         $menuName === ''
         || $location === ''
         || !is_array($items)
      ) {
         return;
      }

      /*
       * 1. Revisar si la ubicación ya tiene
       *    un menú asignado.
       *
       * Si lo tiene, respetamos esa decisión
       * administrativa y no hacemos nada más.
       */
      $locations =
         get_theme_mod(
            'nav_menu_locations',
            []
         );

      $assignedMenuId =
         (int) (
            $locations[$location]
            ?? 0
         );

      if ($assignedMenuId > 0) {

         $assignedMenu =
            wp_get_nav_menu_object(
               $assignedMenuId
            );

         if (
            $assignedMenu
            instanceof \WP_Term
         ) {
            return;
         }
      }

      /*
       * 2. La ubicación está libre.
       *
       * Buscamos si ya existe un menú
       * con el nombre configurado.
       */
      $menuObject =
         wp_get_nav_menu_object(
            $menuName
         );

      /*
       * 3. Si el menú ya existe,
       *    lo reutilizamos.
       *
       * No modificamos sus elementos.
       */
      if (
         $menuObject
         instanceof \WP_Term
      ) {
         $menuId =
            (int) $menuObject->term_id;

         $locations[$location] =
            $menuId;

         set_theme_mod(
            'nav_menu_locations',
            $locations
         );

         return;
      }

      /*
       * 4. El menú no existe.
       *
       * Esta es una instalación inicial:
       * lo creamos.
       */
      $menuId =
         wp_create_nav_menu(
            $menuName
         );

      if (is_wp_error($menuId)) {
         return;
      }

      $menuId = (int) $menuId;

      /*
       * 5. Como el menú acaba de ser creado,
       *    agregamos sus elementos iniciales.
       */
      foreach ($items as $pageKey) {

         /*
          * Login / Logout será dinámico.
          */
         if ($pageKey === 'login') {
            continue;
         }

         $pageConfig =
            $this->config['pages'][$pageKey]
            ?? [];

         if (!is_array($pageConfig)) {
            continue;
         }

         $enabled = (bool) (
            $pageConfig['enabled']
            ?? false
         );

         $required = (bool) (
            $pageConfig['required']
            ?? false
         );

         if (
            !$enabled
            && !$required
         ) {
            continue;
         }

         $slug = sanitize_title(
            (string) (
               $pageConfig['slug']
               ?? ''
            )
         );

         if ($slug === '') {
            continue;
         }

         $page =
            get_page_by_path(
               $slug,
               OBJECT,
               'page'
            );

         if (!$page instanceof \WP_Post) {
            continue;
         }

         wp_update_nav_menu_item(
            $menuId,
            0,
            [
               'menu-item-object-id' =>
                  (int) $page->ID,

               'menu-item-object' =>
                  'page',

               'menu-item-type' =>
                  'post_type',

               'menu-item-status' =>
                  'publish',
            ]
         );
      }

      /*
       * 6. Finalmente asignamos el nuevo menú
       *    a la ubicación pública.
       */
      $locations[$location] =
         $menuId;

      set_theme_mod(
         'nav_menu_locations',
         $locations
      );
   }

   /**
    * Provisiona el menú principal para
    * usuarios autenticados.
    *
    * Reglas:
    * - Si la ubicación ya tiene un menú asignado,
    *   se respeta completamente.
    * - Si no hay menú asignado pero existe uno
    *   con el nombre configurado, se reutiliza
    *   sin modificar sus elementos.
    * - Solo cuando el menú no existe se crea
    *   y se agregan los elementos iniciales.
    */
   public function provision_principal_menu(): void
   {
      $menuConfig =
         $this->config['menus']['principal']
         ?? [];

      if (!is_array($menuConfig)) {
         return;
      }

      $enabled = (bool) (
         $menuConfig['enabled']
         ?? false
      );

      if (!$enabled) {
         return;
      }

      $menuName = sanitize_text_field(
         (string) (
            $menuConfig['name']
            ?? ''
         )
      );

      $location = sanitize_key(
         (string) (
            $menuConfig['location']
            ?? ''
         )
      );

      $items =
         $menuConfig['items']
         ?? [];

      if (
         $menuName === ''
         || $location === ''
         || !is_array($items)
      ) {
         return;
      }

      /*
       * 1. Si la ubicación ya tiene un menú
       *    válido asignado, lo respetamos.
       */
      $locations =
         get_theme_mod(
            'nav_menu_locations',
            []
         );

      $assignedMenuId =
         (int) (
            $locations[$location]
            ?? 0
         );

      if ($assignedMenuId > 0) {
         $assignedMenu =
            wp_get_nav_menu_object(
               $assignedMenuId
            );

         if (
            $assignedMenu
            instanceof \WP_Term
         ) {
            return;
         }
      }

      /*
       * 2. La ubicación está libre.
       *    Buscamos el menú configurado.
       */
      $menuObject =
         wp_get_nav_menu_object(
            $menuName
         );

      /*
       * 3. Si ya existe, lo reutilizamos
       *    sin modificar sus elementos.
       */
      if (
         $menuObject
         instanceof \WP_Term
      ) {
         $menuId =
            (int) $menuObject->term_id;

         $locations[$location] =
            $menuId;

         set_theme_mod(
            'nav_menu_locations',
            $locations
         );

         return;
      }

      /*
       * 4. Si no existe, lo creamos.
       */
      $menuId =
         wp_create_nav_menu(
            $menuName
         );

      if (is_wp_error($menuId)) {
         return;
      }

      $menuId = (int) $menuId;

      /*
       * 5. Como acaba de ser creado,
       *    agregamos sus elementos iniciales.
       */
      foreach ($items as $pageKey) {

         $pageConfig =
            $this->config['pages'][$pageKey]
            ?? [];

         if (!is_array($pageConfig)) {
            continue;
         }

         $enabled = (bool) (
            $pageConfig['enabled']
            ?? false
         );

         $required = (bool) (
            $pageConfig['required']
            ?? false
         );

         if (
            !$enabled
            && !$required
         ) {
            continue;
         }

         $slug = sanitize_title(
            (string) (
               $pageConfig['slug']
               ?? ''
            )
         );

         if ($slug === '') {
            continue;
         }

         $page =
            get_page_by_path(
               $slug,
               OBJECT,
               'page'
            );

         if (!$page instanceof \WP_Post) {
            continue;
         }

         wp_update_nav_menu_item(
            $menuId,
            0,
            [
               'menu-item-object-id' =>
                  (int) $page->ID,

               'menu-item-object' =>
                  'page',

               'menu-item-type' =>
                  'post_type',

               'menu-item-status' =>
                  'publish',
            ]
         );
      }

      /*
       * 6. Asignamos el nuevo menú
       *    a la ubicación principal.
       */
      $locations[$location] =
         $menuId;

      set_theme_mod(
         'nav_menu_locations',
         $locations
      );
   }

   /**
    * Provisiona las páginas declaradas
    * por los módulos activos.
    */
   public function provision_module_pages(): void
   {
      $moduleRegistry =
         \FWK\Modules\Core\Registry\ModuleRegistry::get_instance();

      foreach ($moduleRegistry->all() as $module) {

         $pages =
            $module->manifest()->get_pages();

         if (!is_array($pages)) {
            continue;
         }

         foreach ($pages as $slug => $pageConfig) {

            $slug =
               sanitize_title(
                  (string) $slug
               );

            if (
               $slug === ''
               || !is_array($pageConfig)
            ) {
               continue;
            }

            $title =
               sanitize_text_field(
                  (string) (
                     $pageConfig['title']
                     ?? ''
                  )
               );

            if ($title === '') {
               continue;
            }

            /*
             * Si ya existe una página con ese slug,
             * respetamos la existente.
             */
            $existingPage =
               get_page_by_path(
                  $slug,
                  OBJECT,
                  'page'
               );

            if (
               $existingPage
               instanceof \WP_Post
            ) {
               continue;
            }

            wp_insert_post([
               'post_type' => 'page',
               'post_status' => 'publish',
               'post_title' => $title,
               'post_name' => $slug,
               'post_content' => '',
            ]);
         }
      }
   }

   /**
    * Ejecuta el provisioning base de la aplicación.
    */
   public function provision(): void
   {
      $this->provision_pages();

      $this->provision_module_pages();

      $this->provision_public_menu();

      $this->provision_principal_menu();
   }
}