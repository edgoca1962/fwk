<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Debug;

use FWK\Modules\Core\Context\RequestContext;
use FWK\Modules\Core\Context\ViewContext;
use FWK\Modules\Core\Core;
use FWK\Modules\Core\Contracts\ModuleInterface;
use FWK\Modules\Core\Registry\ModuleRegistry;
use FWK\Modules\Core\Support\Singleton;
use FWK\Modules\Core\Registry\PostTypeRegistry;
use FWK\Modules\Core\Registry\TaxonomyRegistry;
use FWK\Modules\Core\Registry\MetaRegistry;
use FWK\Modules\Core\Registry\MetaBoxRegistry;
use FWK\Modules\SGF\Service\OwnershipService;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Panel de diagnóstico de WP FRW.
 *
 * Muestra información del kernel, módulos, solicitud
 * y contexto visual durante el desarrollo.
 *
 * @package FWK
 */
final class DebugInspector
{
   use Singleton;

   /**
    * Configuración del Inspector.
    *
    * @var array<string, mixed>
    */
   private array $config = [];

   /**
    * Momento aproximado de inicialización del Inspector.
    */
   private float $startedAt;

   protected function __construct()
   {
      $this->startedAt = microtime(true);

      $this->config = $this->load_config();

      /*
       * Registramos siempre el callback.
       *
       * La validación de WP_DEBUG, configuración y permisos
       * se realizará cuando WordPress ejecute wp_footer,
       * momento en el que el usuario ya está completamente resuelto.
       */
      add_action(
         'wp_footer',
         [$this, 'render'],
         9999
      );
   }
   /**
    * Determina si el Inspector puede mostrarse.
    */
   public function is_enabled(): bool
   {
      if (
         !defined('WP_DEBUG')
         || WP_DEBUG !== true
      ) {
         return false;
      }

      if (!(bool) ($this->config['enabled'] ?? false)) {
         return false;
      }

      $capability = sanitize_key(
         (string) ($this->config['capability'] ?? 'manage_options')
      );

      if (
         $capability === ''
         || !current_user_can($capability)
      ) {
         return false;
      }

      /**
       * Permite activar o desactivar el Inspector dinámicamente.
       */
      return (bool) apply_filters(
         'fwk_debug_inspector_enabled',
         true,
         $this
      );
   }

   /**
    * Renderiza el panel.
    */
   public function render(): void
   {
      if (!$this->is_enabled()) {
         return;
      }

      $core = Core::get_instance();

      $registry = $core->modules();

      $postTypes =
         PostTypeRegistry::get_instance();

      $taxonomies =
         TaxonomyRegistry::get_instance();

      $metaRegistry =
         MetaRegistry::get_instance();

      $metaBoxRegistry =
         MetaBoxRegistry::get_instance();

      $request = $core->request();

      $view = $core->resolve_view();

      $sections = $this->get_sections();

      $expanded = (bool) (
         $this->config['expanded']
         ?? false
      );

      $position = $this->get_position();

      ?>

      <aside id="fwk-debug-inspector" class="fwk-debug-inspector fwk-debug-inspector--<?= esc_attr($position); ?>" aria-label="<?php esc_attr_e(
           'Inspector de WP FRW',
           'FWK'
        ); ?>">

         <details <?= $expanded ? 'open' : ''; ?>>

            <summary>

               <strong>
                  WP FRW Inspector
               </strong>

               <span class="fwk-debug-status">
                  <?= esc_html(
                     sprintf(
                        '%s · %s',
                        strtoupper(
                           $request->get_type()
                        ),
                        $view->string(
                           'modulo',
                           'sin módulo'
                        )
                     )
                  ); ?>
               </span>

            </summary>

            <div class="fwk-debug-content">

               <?php if ($sections['core']): ?>
                  <?php
                  $this->render_core_section(
                     $core
                  );
                  ?>
               <?php endif; ?>

               <?php if ($sections['modules']): ?>
                  <?php
                  $this->render_modules_section(
                     $registry
                  );
                  ?>
               <?php endif; ?>

               <?php if ($sections['post_types']): ?>
                  <?php
                  $this->render_post_types_section(
                     $postTypes
                  );
                  ?>
               <?php endif; ?>

               <?php if ($sections['taxonomies']): ?>
                  <?php
                  $this->render_taxonomies_section(
                     $taxonomies
                  );
                  ?>
               <?php endif; ?>

               <?php if ($sections['metadata']): ?>
                  <?php
                  $this->render_meta_section(
                     $metaRegistry
                  );
                  ?>
               <?php endif; ?>

               <?php if ($sections['metaboxes']): ?>
                  <?php
                  $this->render_metaboxes_section(
                     $metaBoxRegistry
                  );
                  ?>

               <?php endif; ?>

               <?php if ($sections['request']): ?>
                  <?php
                  $this->render_request_section(
                     $request
                  );
                  ?>
               <?php endif; ?>

               <?php if ($sections['ownership']): ?>
                  <?php
                  $this->render_ownership_section();
                  ?>
               <?php endif; ?>

               <?php if ($sections['view']): ?>
                  <?php
                  $this->render_view_section(
                     $view
                  );
                  ?>
               <?php endif; ?>

               <?php if (
                  $sections['view_history']
               ): ?>
                  <?php
                  $this->render_view_history_section(
                     $view
                  );
                  ?>
               <?php endif; ?>

            </div>

         </details>

      </aside>

      <?php

      $this->render_styles();
   }
   /**
    * Sección del Core.
    */
   private function render_core_section(
      Core $core
   ): void {
      $elapsed = microtime(true) - $this->startedAt;

      $rows = [
         'Estado' => $core->is_booted()
            ? 'Inicializado'
            : 'No inicializado',

         'Clase' => $core::class,

         'Tiempo Inspector' => number_format(
            $elapsed * 1000,
            2
         ) . ' ms',

         'Memoria actual' => size_format(
            memory_get_usage(true)
         ),

         'Memoria máxima' => size_format(
            memory_get_peak_usage(true)
         ),
      ];

      $this->render_section(
         __('Core', 'FWK'),
         $rows
      );
   }
   /**
    * Sección de módulos.
    */

   private function render_modules_section(
      ModuleRegistry $registry
   ): void {
      ?>
      <section class="fwk-debug-section">
         <h3>
            <?= esc_html__('Módulos', 'FWK'); ?>
         </h3>
         <?php
         $errors = $registry->get_validation_errors();
         $warnings = $registry->get_validation_warnings();
         ?>

         <div class="fwk-debug-table-wrapper">
            <table>
               <tbody>
                  <tr>
                     <th>
                        <?= esc_html__('Estado Registry', 'FWK'); ?>
                     </th>

                     <td>
                        <?php if ($registry->is_booted()): ?>
                           <span class="fwk-debug-ok">
                              <?= esc_html__('Inicializado', 'FWK'); ?>
                           </span>
                        <?php else: ?>
                           <span class="fwk-debug-warning">
                              <?= esc_html__('No inicializado', 'FWK'); ?>
                           </span>
                        <?php endif; ?>
                     </td>
                  </tr>

                  <tr>
                     <th>
                        <?= esc_html__('Errores', 'FWK'); ?>
                     </th>

                     <td>
                        <?php if ($errors === []): ?>
                           <span class="fwk-debug-ok">
                              <?= esc_html__('Ninguno', 'FWK'); ?>
                           </span>
                        <?php else: ?>
                           <?= esc_html((string) count($errors)); ?>
                        <?php endif; ?>
                     </td>
                  </tr>

                  <tr>
                     <th>
                        <?= esc_html__('Advertencias', 'FWK'); ?>
                     </th>

                     <td>
                        <?php if ($warnings === []): ?>
                           <span class="fwk-debug-ok">
                              <?= esc_html__('Ninguna', 'FWK'); ?>
                           </span>
                        <?php else: ?>
                           <?= esc_html((string) count($warnings)); ?>
                        <?php endif; ?>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>

         <?php if ($registry->get_validation_errors() !== []): ?>

            <div class="fwk-debug-validation fwk-debug-validation--error">
               <h4>
                  <?= esc_html__(
                     'Errores de módulos',
                     'FWK'
                  ); ?>
               </h4>

               <ul>
                  <?php foreach (
                     $registry->get_validation_errors()
                     as $error
                  ): ?>
                     <li>
                        <?= esc_html($error); ?>
                     </li>
                  <?php endforeach; ?>
               </ul>
            </div>

         <?php endif; ?>

         <?php if ($registry->get_validation_warnings() !== []): ?>

            <div class="fwk-debug-validation fwk-debug-validation--warning">
               <h4>
                  <?= esc_html__(
                     'Advertencias de módulos',
                     'FWK'
                  ); ?>
               </h4>

               <ul>
                  <?php foreach (
                     $registry->get_validation_warnings()
                     as $warning
                  ): ?>
                     <li>
                        <?= esc_html($warning); ?>
                     </li>
                  <?php endforeach; ?>
               </ul>
            </div>

         <?php endif; ?>

         <?php if ($registry->all() === []): ?>

            <p class="fwk-debug-warning">
               <?= esc_html__(
                  'No existen módulos registrados.',
                  'FWK'
               ); ?>
            </p>

         <?php else: ?>
            <?php
            $bootOrder = $registry->get_boot_order();
            ?>

            <?php if ($bootOrder !== []): ?>
               <p>
                  <strong>
                     <?= esc_html__(
                        'Orden de arranque:',
                        'FWK'
                     ); ?>
                  </strong>

                  <?= esc_html(
                     implode(' → ', $bootOrder)
                  ); ?>
               </p>
            <?php endif; ?>

            <div class="fwk-debug-table-wrapper">
               <table>
                  <thead>
                     <tr>
                        <th>
                           <?= esc_html__('Slug', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Nombre', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Clase', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Post types', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Páginas', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Versión', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Dependencias', 'FWK'); ?>
                        </th>
                     </tr>
                  </thead>

                  <tbody>
                     <?php foreach ($registry->all() as $slug => $module): ?>
                        <?php
                        if (!$module instanceof ModuleInterface) {
                           continue;
                        }
                        ?>
                        <tr>
                           <td>
                              <?= esc_html($slug); ?>
                           </td>

                           <td>
                              <?= esc_html($module->get_name()); ?>
                           </td>

                           <td>
                              <code>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  <?= esc_html($module::class); ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               </code>
                           </td>

                           <td>
                              <?= esc_html(
                                 implode(
                                    ', ',
                                    $module->get_post_types()
                                 )
                              ); ?>
                           </td>

                           <td>
                              <?= esc_html(
                                 implode(
                                    ', ',
                                    $module->get_pages()
                                 )
                              ); ?>
                           </td>

                           <td>
                              <?= esc_html(
                                 $module->manifest()->get_version()
                              ); ?>
                           </td>

                           <td>
                              <?= esc_html(
                                 implode(
                                    ', ',
                                    $module->manifest()->get_dependencies()
                                 )
                              ); ?>
                           </td>
                        </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>

         <?php endif; ?>
      </section>
      <?php
   }
   /**
    * Summary of render_post_types_section
    * @param PostTypeRegistry $registry
    * @return void
    */
   private function render_post_types_section(
      PostTypeRegistry $registry
   ): void {
      ?>
      <section class="fwk-debug-section">
         <h3>
            <?= esc_html__('Post Types', 'FWK'); ?>
         </h3>

         <div class="fwk-debug-table-wrapper">
            <table>
               <thead>
                  <tr>
                     <th>Slug</th>
                     <th>Módulo</th>
                     <th>Estado</th>
                     <th>Native</th>
                     <th>REST</th>
                  </tr>
               </thead>

               <tbody>

                  <?php foreach (
                     $registry->all()
                     as $slug => $definition
                  ): ?>

                     <?php
                     $registered = in_array(
                        $slug,
                        $registry->get_registered(),
                        true
                     );
                     ?>

                     <tr>
                        <td>
                           <code><?= esc_html($slug); ?></code>
                        </td>

                        <td>
                           <?= esc_html(
                              $registry->get_owner($slug) ?? '—'
                           ); ?>
                        </td>

                        <td>
                           <?= esc_html(
                              $registered
                              ? 'Registrado'
                              : (
                                 $definition->is_native()
                                 ? 'Nativo'
                                 : (
                                    $definition->is_enabled()
                                    ? 'Pendiente'
                                    : 'Deshabilitado'
                                 )
                              )
                           ); ?>
                        </td>

                        <td>
                           <?= $definition->is_native()
                              ? 'Sí'
                              : 'No'; ?>
                        </td>

                        <td>
                           <?= !empty(
                              $definition->get_args()['show_in_rest']
                           )
                              ? 'Sí'
                              : 'No'; ?>
                        </td>
                     </tr>

                  <?php endforeach; ?>

               </tbody>
            </table>
         </div>
      </section>
      <?php
   }
   /**
    * Summary of render_taxonomies_section
    * @param TaxonomyRegistry $registry
    * @return void
    */
   private function render_taxonomies_section(
      TaxonomyRegistry $registry
   ): void {
      ?>
      <section class="fwk-debug-section">
         <h3>
            <?= esc_html__('Taxonomías', 'FWK'); ?>
         </h3>

         <div class="fwk-debug-table-wrapper">
            <table>
               <thead>
                  <tr>
                     <th>Slug</th>
                     <th>Módulo</th>
                     <th>Estado</th>
                     <th>Jerárquica</th>
                     <th>Object types</th>
                     <th>REST</th>
                  </tr>
               </thead>

               <tbody>

                  <?php foreach (
                     $registry->all()
                     as $slug => $definition
                  ): ?>

                     <?php
                     $registered = in_array(
                        $slug,
                        $registry->get_registered(),
                        true
                     );

                     $args = $definition->get_args();
                     ?>

                     <tr>
                        <td>
                           <code>
                                                                                                                                                                                                                                                                                                                                                                                                                     <?= esc_html($slug); ?>
                                                                                                                                                                                                                                                                                                                                                                                                                  </code>
                        </td>

                        <td>
                           <?= esc_html(
                              $registry->get_owner($slug)
                              ?? '—'
                           ); ?>
                        </td>

                        <td>
                           <?php
                           echo esc_html(
                              $definition->is_native()
                              ? 'Nativa'
                              : (
                                 !$definition->is_enabled()
                                 ? 'Deshabilitada'
                                 : (
                                    $registered
                                    ? 'Registrada'
                                    : 'Pendiente'
                                 )
                              )
                           );
                           ?>
                        </td>

                        <td>
                           <?= $definition->is_hierarchical()
                              ? 'Sí'
                              : 'No'; ?>
                        </td>

                        <td>
                           <?= esc_html(
                              implode(
                                 ', ',
                                 $registry->get_object_types(
                                    $slug
                                 )
                              )
                           ); ?>
                        </td>

                        <td>
                           <?= !empty($args['show_in_rest'])
                              ? 'Sí'
                              : 'No'; ?>
                        </td>
                     </tr>

                  <?php endforeach; ?>

               </tbody>
            </table>
         </div>

         <?php if ($registry->get_errors() !== []): ?>
            <h4>Errores</h4>

            <ul>
               <?php foreach (
                  $registry->get_errors()
                  as $error
               ): ?>
                  <li>
                     <?= esc_html($error); ?>
                  </li>
               <?php endforeach; ?>
            </ul>
         <?php endif; ?>

         <?php if ($registry->get_warnings() !== []): ?>
            <h4>Advertencias</h4>

            <ul>
               <?php foreach (
                  $registry->get_warnings()
                  as $warning
               ): ?>
                  <li>
                     <?= esc_html($warning); ?>
                  </li>
               <?php endforeach; ?>
            </ul>
         <?php endif; ?>

      </section>
      <?php
   }
   /**
    * Sección Temporal Metadata.
    */
   private function render_meta_section(
      MetaRegistry $registry
   ): void {
      ?>
      <section class="fwk-debug-section">

         <h3>
            <?= esc_html__(
               'Metadata',
               'FWK'
            ); ?>
         </h3>

         <div class="fwk-debug-table-wrapper">

            <table>
               <thead>
                  <tr>
                     <th>Post Type</th>
                     <th>Meta key</th>
                     <th>Módulo</th>
                     <th>Tipo</th>
                     <th>Single</th>
                     <th>REST</th>
                     <th>Estado</th>
                  </tr>
               </thead>

               <tbody>

                  <?php foreach (
                     $registry->all()
                     as $postType => $definitions
                  ): ?>

                     <?php foreach (
                        $definitions
                        as $key => $definition
                     ): ?>

                        <?php
                        $registered = in_array(
                           $key,
                           $registry->get_registered(
                              $postType
                           ),
                           true
                        );
                        ?>

                        <tr>

                           <td>
                              <code>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <?= esc_html($postType); ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             </code>
                           </td>

                           <td>
                              <code>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <?= esc_html($key); ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             </code>
                           </td>

                           <td>
                              <?= esc_html(
                                 $registry->get_owner(
                                    $postType,
                                    $key
                                 ) ?? '—'
                              ); ?>
                           </td>

                           <td>
                              <?= esc_html(
                                 $definition->get_type()
                              ); ?>
                           </td>

                           <td>
                              <?= $definition->is_single()
                                 ? 'Sí'
                                 : 'No'; ?>
                           </td>

                           <td>
                              <?= $definition->show_in_rest()
                                 ? 'Sí'
                                 : 'No'; ?>
                           </td>

                           <td>
                              <?= esc_html(
                                 !$definition->is_enabled()
                                 ? 'Deshabilitado'
                                 : (
                                    $registered
                                    ? 'Registrado'
                                    : 'Pendiente'
                                 )
                              ); ?>
                           </td>

                        </tr>

                     <?php endforeach; ?>

                  <?php endforeach; ?>

               </tbody>
            </table>

         </div>

         <?php if ($registry->get_errors() !== []): ?>

            <h4>Errores</h4>

            <ul>
               <?php foreach (
                  $registry->get_errors()
                  as $error
               ): ?>
                  <li>
                     <?= esc_html($error); ?>
                  </li>
               <?php endforeach; ?>
            </ul>

         <?php endif; ?>

         <?php if (
            $registry->get_warnings() !== []
         ): ?>

            <h4>Advertencias</h4>

            <ul>
               <?php foreach (
                  $registry->get_warnings()
                  as $warning
               ): ?>
                  <li>
                     <?= esc_html($warning); ?>
                  </li>
               <?php endforeach; ?>
            </ul>

         <?php endif; ?>

      </section>
      <?php
   }
   /**
    * Sección de Metaboxes.
    */
   private function render_metaboxes_section(
      MetaBoxRegistry $registry
   ): void {
      ?>
      <section class="fwk-debug-section">

         <h3>
            <?= esc_html__(
               'Metaboxes',
               'FWK'
            ); ?>
         </h3>

         <div class="fwk-debug-table-wrapper">

            <table>

               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Módulo</th>
                     <th>Post Types</th>
                     <th>Contexto</th>
                     <th>Prioridad</th>
                     <th>Campos</th>
                     <th>Estado</th>
                  </tr>
               </thead>

               <tbody>

                  <?php foreach (
                     $registry->all()
                     as $id => $definition
                  ): ?>

                     <?php
                     $registered = in_array(
                        $id,
                        $registry->get_registered(),
                        true
                     );

                     $fields = array_keys(
                        $definition->get_fields()
                     );
                     ?>

                     <tr>

                        <td>
                           <code>
                                                                                                                                                                                                                              <?= esc_html($id); ?>
                                                                                                                                                                                                                           </code>
                        </td>

                        <td>
                           <?= esc_html(
                              $registry->get_owner($id)
                              ?? '—'
                           ); ?>
                        </td>

                        <td>
                           <?= esc_html(
                              implode(
                                 ', ',
                                 $definition->get_post_types()
                              )
                           ); ?>
                        </td>

                        <td>
                           <?= esc_html(
                              $definition->get_context()
                           ); ?>
                        </td>

                        <td>
                           <?= esc_html(
                              $definition->get_priority()
                           ); ?>
                        </td>

                        <td>
                           <?= esc_html(
                              $fields === []
                              ? '—'
                              : implode(', ', $fields)
                           ); ?>
                        </td>

                        <td>
                           <?= esc_html(
                              !$definition->is_enabled()
                              ? 'Deshabilitado'
                              : (
                                 $registered
                                 ? 'Registrado'
                                 : (
                                    $registry->is_booted()
                                    ? 'Configurado'
                                    : 'Pendiente'
                                 )
                              )
                           ); ?>
                        </td>

                     </tr>

                  <?php endforeach; ?>

               </tbody>

            </table>

         </div>

         <?php if ($registry->get_errors() !== []): ?>

            <div class="fwk-debug-validation fwk-debug-validation--error">

               <h4>
                  <?= esc_html__(
                     'Errores',
                     'FWK'
                  ); ?>
               </h4>

               <ul>

                  <?php foreach (
                     $registry->get_errors()
                     as $error
                  ): ?>

                     <li>
                        <?= esc_html($error); ?>
                     </li>

                  <?php endforeach; ?>

               </ul>

            </div>

         <?php endif; ?>

         <?php if ($registry->get_warnings() !== []): ?>

            <div class="fwk-debug-validation fwk-debug-validation--warning">

               <h4>
                  <?= esc_html__(
                     'Advertencias',
                     'FWK'
                  ); ?>
               </h4>

               <ul>

                  <?php foreach (
                     $registry->get_warnings()
                     as $warning
                  ): ?>

                     <li>
                        <?= esc_html($warning); ?>
                     </li>

                  <?php endforeach; ?>

               </ul>

            </div>

         <?php endif; ?>

      </section>
      <?php
   }
   /**
    * Sección de RequestContext.
    */
   private function render_request_section(
      RequestContext $request
   ): void {
      $data = $request->to_array();

      /*
       * Los flags se presentan en una sección independiente
       * para facilitar la lectura.
       */
      $flags = $data['flags'] ?? [];

      unset($data['flags']);

      $this->render_section(
         __('RequestContext', 'FWK'),
         $data
      );

      if (is_array($flags)) {
         $activeFlags = array_keys(
            array_filter($flags)
         );

         $this->render_section(
            __('Request flags activos', 'FWK'),
            [
               'flags' => $activeFlags === []
                  ? 'Ninguno'
                  : implode(', ', $activeFlags),
            ]
         );
      }
   }
   /**
    * Muestra información de Ownership
    * para el post actualmente consultado.
    */
   private function render_ownership_section(): void
   {
      $postId = get_queried_object_id();

      if ($postId <= 0) {
         $this->render_section(
            __('Ownership', 'FWK'),
            [
               'Estado' =>
                  'No existe un post individual en esta solicitud.',
            ]
         );

         return;
      }

      $post = get_post($postId);

      if (!$post instanceof \WP_Post) {
         $this->render_section(
            __('Ownership', 'FWK'),
            [
               'Post ID' => $postId,
               'Estado' =>
                  'El objeto consultado no es un WP_Post.',
            ]
         );

         return;
      }

      $ownership =
         OwnershipService::get_instance();

      /*
       * Solo mostramos esta sección para
       * Post Types sujetos a ownership.
       */
      if (
         !$ownership->is_owned_post_type(
            $post->post_type
         )
      ) {
         $this->render_section(
            __('Ownership', 'FWK'),
            [
               'Post ID' => $postId,
               'Post Type' => $post->post_type,
               'Estado' =>
                  'El Post Type no está sujeto a Ownership SGF.',
            ]
         );

         return;
      }

      $currentUserId =
         get_current_user_id();

      $ownerId =
         $ownership->get_owner_id(
            $postId
         );

      $isOwner =
         $ownership->is_owner(
            $postId
         );

      $canAccess =
         $ownership->can_access(
            $postId
         );

      $isMovement =
         $ownership->is_movement_post_type(
            $post->post_type
         );

      $walletId = 0;
      $walletOwnerId = 0;
      $movementIntegrity = null;

      /*
       * Libro y Banco heredan relación
       * estructural con una Billetera.
       */
      if ($isMovement) {
         $walletId =
            (int) $post->post_parent;

         if ($walletId > 0) {
            $wallet = get_post(
               $walletId
            );

            if (
               $wallet instanceof \WP_Post
               && $wallet->post_type === 'billetera'
            ) {
               $walletOwnerId =
                  (int) $wallet->post_author;
            }
         }

         $movementIntegrity =
            $ownership
               ->validate_movement_ownership(
                  $postId
               );
      }

      ?>

      <section class="fwk-debug-section">

         <h3>
            <?= esc_html__(
               'Ownership',
               'FWK'
            ); ?>
         </h3>

         <table>

            <tbody>

               <tr>
                  <th>Post ID</th>
                  <td>
                     <?= esc_html(
                        (string) $postId
                     ); ?>
                  </td>
               </tr>

               <tr>
                  <th>Post Type</th>
                  <td>
                     <code>
                                                                                    <?= esc_html(
                                                                                       $post->post_type
                                                                                    ); ?>
                                                                                 </code>
                  </td>
               </tr>

               <tr>
                  <th>Usuario actual</th>
                  <td>
                     <?= esc_html(
                        (string) $currentUserId
                     ); ?>
                  </td>
               </tr>

               <tr>
                  <th>Post Author</th>
                  <td>
                     <?= esc_html(
                        (string) $ownerId
                     ); ?>
                  </td>
               </tr>

               <tr>
                  <th>Propietario</th>
                  <td>
                     <?= esc_html(
                        $isOwner
                        ? 'Sí'
                        : 'No'
                     ); ?>
                  </td>
               </tr>

               <?php if ($isMovement): ?>

                  <tr>
                     <th>Post Parent</th>
                     <td>
                        <?= esc_html(
                           (string) $walletId
                        ); ?>
                     </td>
                  </tr>

                  <tr>
                     <th>Autor billetera</th>
                     <td>
                        <?= esc_html(
                           $walletOwnerId > 0
                           ? (string) $walletOwnerId
                           : '—'
                        ); ?>
                     </td>
                  </tr>

                  <tr>
                     <th>Integridad movimiento</th>
                     <td>
                        <?= esc_html(
                           $movementIntegrity
                           ? 'Válida'
                           : 'Inválida'
                        ); ?>
                     </td>
                  </tr>

               <?php endif; ?>

               <tr>
                  <th>Acceso</th>
                  <td>
                     <?= esc_html(
                        $canAccess
                        ? 'Permitido'
                        : 'Denegado'
                     ); ?>
                  </td>
               </tr>

            </tbody>

         </table>

      </section>

      <?php
   }
   /**
    * Sección de ViewContext.
    */
   private function render_view_section(
      ViewContext $view
   ): void {
      $importantKeys = [
         'modulo',
         'request_type',
         'postType',
         'page_slug',
         'post_slug',
         'taxonomy',
         'term_slug',
         'titulo',
         'subtitulo',
         'main',
         'article',
         'asideL',
         'asideR',
         't_navbar',
         't_banner',
         't_main',
         't_none',
         't_asideL',
         't_asideR',
         't_footer',
         'paginacion',
         'comentarios',
         'show_content',
      ];

      $rows = [];

      foreach ($importantKeys as $key) {
         $rows[$key] = $view->get(
            $key,
            '—'
         );
      }

      $this->render_section(
         __('ViewContext', 'FWK'),
         $rows
      );
   }

   /**
    * Historial de cambios de ViewContext.
    */
   private function render_view_history_section(
      ViewContext $view
   ): void {
      $history = $view->history();

      ?>
      <section class="fwk-debug-section">
         <h3>
            <?= esc_html__('Historial de ViewContext', 'FWK'); ?>
         </h3>

         <?php if ($history === []): ?>

            <p>
               <?= esc_html__(
                  'No se registraron modificaciones.',
                  'FWK'
               ); ?>
            </p>

         <?php else: ?>

            <?php foreach ($history as $index => $entry): ?>
               <details class="fwk-debug-history-entry">
                  <summary>
                     <?= esc_html(
                        sprintf(
                           '#%d · %s',
                           $index + 1,
                           (string) ($entry['source'] ?? 'desconocido')
                        )
                     ); ?>
                  </summary>

                  <pre><?= esc_html(
                     $this->format_value(
                        $entry['values'] ?? []
                     )
                  ); ?></pre>
               </details>
            <?php endforeach; ?>

         <?php endif; ?>
      </section>
      <?php
   }

   /**
    * Renderiza una sección genérica.
    *
    * @param array<string, mixed> $rows
    */
   private function render_section(
      string $title,
      array $rows
   ): void {
      ?>
      <section class="fwk-debug-section">
         <h3>
            <?= esc_html($title); ?>
         </h3>

         <div class="fwk-debug-table-wrapper">
            <table>
               <tbody>
                  <?php foreach ($rows as $label => $value): ?>
                     <tr>
                        <th>
                           <?= esc_html((string) $label); ?>
                        </th>

                        <td>
                           <?php $this->render_value($value); ?>
                        </td>
                     </tr>
                  <?php endforeach; ?>
               </tbody>
            </table>
         </div>
      </section>
      <?php
   }
   /**
    * Renderiza un valor de forma segura.
    */
   private function render_value(
      mixed $value
   ): void {
      if (is_bool($value)) {
         echo $value
            ? '<span class="fwk-debug-ok">true</span>'
            : '<span class="fwk-debug-muted">false</span>';

         return;
      }

      if ($value === null || $value === '') {
         echo '<span class="fwk-debug-muted">—</span>';

         return;
      }

      if (is_array($value) || is_object($value)) {
         ?>
         <pre><?= esc_html(
            $this->format_value($value)
         ); ?></pre>
         <?php

         return;
      }

      echo esc_html((string) $value);
   }

   /**
    * Convierte valores complejos a JSON legible.
    */
   private function format_value(
      mixed $value
   ): string {
      $encoded = wp_json_encode(
         $value,
         JSON_PRETTY_PRINT
         | JSON_UNESCAPED_UNICODE
         | JSON_UNESCAPED_SLASHES
      );

      return is_string($encoded)
         ? $encoded
         : '';
   }

   /**
    * Devuelve las secciones habilitadas.
    *
    * @return array<string, bool>
    */
   private function get_sections(): array
   {
      $defaults = [
         'core' => true,
         'modules' => true,

         'post_types' => true,
         'taxonomies' => true,
         'metadata' => true,
         'metaboxes' => true,

         'request' => true,
         'ownership' => true,

         'view' => true,
         'view_history' => true,
      ];

      $configured = $this->config['sections'] ?? [];

      if (!is_array($configured)) {
         return $defaults;
      }

      return array_map(
         static fn(mixed $value): bool => (bool) $value,
         array_replace(
            $defaults,
            $configured
         )
      );
   }

   /**
    * Devuelve la posición válida.
    */
   private function get_position(): string
   {
      $position = sanitize_key(
         (string) ($this->config['position'] ?? 'bottom')
      );

      return in_array(
         $position,
         ['top', 'bottom'],
         true
      )
         ? $position
         : 'bottom';
   }

   /**
    * Carga la configuración.
    *
    * @return array<string, mixed>
    */
   private function load_config(): array
   {
      $file = get_template_directory()
         . '/config/debug.php';

      if (!is_readable($file)) {
         return [
            'enabled' => false,
         ];
      }

      $config = require $file;

      if (!is_array($config)) {
         return [
            'enabled' => false,
         ];
      }

      return $config;
   }

   /**
    * Estilos básicos del Inspector.
    *
    * Posteriormente pueden moverse a un archivo CSS.
    */
   private function render_styles(): void
   {
      ?>
      <style>
         .fwk-debug-inspector {
            position: fixed;
            right: 1rem;
            left: 1rem;
            z-index: 999999;
            max-height: 75vh;
            overflow: auto;
            color: #f8f9fa;
            background: rgba(20, 20, 24, .98);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: .5rem;
            box-shadow: 0 0 2rem rgba(0, 0, 0, .4);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco,
               Consolas, "Liberation Mono", monospace;
            font-size: 13px;
            line-height: 1.45;
         }

         .fwk-debug-inspector--bottom {
            bottom: 1rem;
         }

         .fwk-debug-inspector--top {
            top: 1rem;
         }

         .fwk-debug-inspector>details>summary {
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            padding: .8rem 1rem;
            cursor: pointer;
            user-select: none;
         }

         .fwk-debug-content {
            padding: 0 1rem 1rem;
         }

         .fwk-debug-status {
            color: #adb5bd;
            font-size: 12px;
         }

         .fwk-debug-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .14);
         }

         .fwk-debug-section h3 {
            margin: 0 0 .6rem;
            color: #fff;
            font-size: 14px;
         }

         .fwk-debug-table-wrapper {
            overflow-x: auto;
         }

         .fwk-debug-inspector table {
            width: 100%;
            border-collapse: collapse;
         }

         .fwk-debug-inspector th,
         .fwk-debug-inspector td {
            padding: .45rem .55rem;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
         }

         .fwk-debug-inspector th {
            width: 190px;
            color: #ced4da;
            font-weight: 600;
         }

         .fwk-debug-inspector td {
            color: #f8f9fa;
         }

         .fwk-debug-inspector pre {
            max-height: 300px;
            margin: 0;
            overflow: auto;
            color: #f8f9fa;
            white-space: pre-wrap;
            word-break: break-word;
         }

         .fwk-debug-history-entry {
            margin-bottom: .5rem;
            padding: .5rem;
            background: rgba(255, 255, 255, .04);
            border-radius: .25rem;
         }

         .fwk-debug-history-entry summary {
            cursor: pointer;
         }

         .fwk-debug-history-entry pre {
            margin-top: .6rem;
         }

         .fwk-debug-ok {
            color: #75b798;
         }

         .fwk-debug-muted {
            color: #868e96;
         }

         .fwk-debug-warning {
            color: #ffda6a;
         }

         .fwk-debug-inspector code {
            color: #6edff6;
         }

         .fwk-debug-validation {
            margin: .75rem 0;
            padding: .75rem 1rem;
            border-radius: .35rem;
         }

         .fwk-debug-validation h4 {
            margin: 0 0 .5rem;
            font-size: 13px;
         }

         .fwk-debug-validation ul {
            margin: 0;
            padding-left: 1.25rem;
         }

         .fwk-debug-validation--error {
            color: #f1aeb5;
            background: rgba(220, 53, 69, .16);
            border: 1px solid rgba(220, 53, 69, .35);
         }

         .fwk-debug-validation--warning {
            color: #ffe69c;
            background: rgba(255, 193, 7, .13);
            border: 1px solid rgba(255, 193, 7, .32);
         }

         @media (max-width: 767px) {
            .fwk-debug-inspector {
               right: .5rem;
               left: .5rem;
               bottom: .5rem;
               max-height: 85vh;
               font-size: 11px;
            }

            .fwk-debug-inspector th {
               width: 120px;

            }
         }

      </style>
      <?php
   }
}