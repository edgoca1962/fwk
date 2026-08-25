<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$config =
   $args['config']
   ?? [];

$filters =
   $args['filters']
   ?? [];

$searchConfig =
   $config['search']
   ?? [];

$taxonomyConfig =
   $config['taxonomies']
   ?? [];

$categoryConfig =
   $taxonomyConfig['category']
   ?? [];

$tagConfig =
   $taxonomyConfig['post_tag']
   ?? [];

$orderConfig =
   $config['order']
   ?? [];

$hasActiveFilters =
   ($filters['search'] ?? '') !== ''
   || ($filters['taxonomies'] ?? []) !== []
   || ($filters['order'] ?? 'DESC') !== 'DESC';

?>

<?php if (
   !empty(
   $searchConfig['enabled']
)
): ?>

   <form method="get" class="mb-4">

      <div class="row g-3">

         <div class="col-12 col-lg-4">

            <div class="input-group">

               <input type="search" name="<?= esc_attr(
                  $searchConfig['param']
                  ?? 'buscar'
               ); ?>" value="<?= esc_attr(
                   $filters['search']
                   ?? ''
                ); ?>" class="form-control" placeholder="<?= esc_attr(
                    $searchConfig['placeholder']
                    ?? ''
                 ); ?>" aria-label="<?= esc_attr(
                     $searchConfig['label']
                     ?? ''
                  ); ?>">

               <button type="submit" class="btn btn-primary">
                  <?= esc_html__(
                     'Buscar',
                     'FWK'
                  ); ?>
               </button>

            </div>

         </div>

         <div class="col-12 col-lg-4">

            <?php if (
               !empty(
               $categoryConfig['enabled']
            )
            ): ?>

               <select name="<?= esc_attr(
                  $categoryConfig['param']
                  ?? 'categoria'
               ); ?>" class="form-select" aria-label="<?= esc_attr(
                   $categoryConfig['label']
                   ?? ''
                ); ?>">

                  <option value="">
                     <?= esc_html(
                        $categoryConfig['label']
                        ?? ''
                     ); ?>
                  </option>

                  <?php

                  $categories =
                     get_terms([
                        'taxonomy' =>
                           'category',

                        'hide_empty' =>
                           true,
                     ]);

                  ?>

                  <?php if (
                     !is_wp_error($categories)
                  ): ?>

                     <?php foreach (
                        $categories
                        as $category
                     ): ?>

                        <option value="<?= esc_attr(
                           $category->slug
                        ); ?>" <?php selected(
                            $filters['taxonomies']['category']
                            ?? '',
                            $category->slug
                         ); ?>>
                           <?= esc_html(
                              $category->name
                           ); ?>
                        </option>

                     <?php endforeach; ?>

                  <?php endif; ?>

               </select>

            <?php endif; ?>

         </div>

         <div class="col-12 col-lg-4">


            <?php if (
               !empty(
               $tagConfig['enabled']
            )
            ): ?>

               <select name="<?= esc_attr(
                  $tagConfig['param']
                  ?? 'tag'
               ); ?>" class="form-select" aria-label="<?= esc_attr(
                   $tagConfig['label']
                   ?? ''
                ); ?>">

                  <option value="">
                     <?= esc_html(
                        $tagConfig['label']
                        ?? ''
                     ); ?>
                  </option>

                  <?php

                  $tags =
                     get_terms([
                        'taxonomy' =>
                           'post_tag',

                        'hide_empty' =>
                           true,
                     ]);

                  ?>

                  <?php if (
                     !is_wp_error($tags)
                  ): ?>

                     <?php foreach (
                        $tags
                        as $tag
                     ): ?>

                        <option value="<?= esc_attr(
                           $tag->slug
                        ); ?>" <?php selected(
                            $filters['taxonomies']['post_tag']
                            ?? '',
                            $tag->slug
                         ); ?>>
                           <?= esc_html(
                              $tag->name
                           ); ?>
                        </option>

                     <?php endforeach; ?>

                  <?php endif; ?>

               </select>

            <?php endif; ?>

         </div>

      </div>

      <div class="row g-3 align-items-end mt-1">

         <div class="col-12 col-md-4">

            <?php if (
               !empty(
               $orderConfig['enabled']
            )
            ): ?>

               <select name="<?= esc_attr(
                  $orderConfig['param']
                  ?? 'orden'
               ); ?>" class="form-select" aria-label="<?= esc_attr__(
                   'Orden',
                   'FWK'
                ); ?>">

                  <option value="DESC" <?php selected(
                     $filters['order']
                     ?? 'DESC',
                     'DESC'
                  ); ?>>
                     <?= esc_html__(
                        'Más recientes',
                        'FWK'
                     ); ?>
                  </option>

                  <option value="ASC" <?php selected(
                     $filters['order']
                     ?? 'DESC',
                     'ASC'
                  ); ?>>
                     <?= esc_html__(
                        'Más antiguos',
                        'FWK'
                     ); ?>
                  </option>

               </select>

            <?php endif; ?>

         </div>

         <div class="col-12 col-md-8 d-flex flex-column flex-sm-row gap-2 justify-content-md-end">

            <button type="submit" class="btn btn-primary">
               <?= esc_html__(
                  'Aplicar filtros',
                  'FWK'
               ); ?>
            </button>

            <?php if ($hasActiveFilters): ?>

               <a href="<?= esc_url(
                  get_post_type_archive_link(
                     'post'
                  )
                  ?: home_url('/blog/')
               ); ?>" class="btn btn-outline-secondary">
                  <?= esc_html__(
                     'Limpiar filtros',
                     'FWK'
                  ); ?>
               </a>

            <?php endif; ?>

         </div>
      </div>

   </form>

<?php endif; ?>
