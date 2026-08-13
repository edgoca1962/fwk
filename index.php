<?php

declare(strict_types=1);

use FWK\Modules\Core\Core;

$view = Core::get_instance()->resolve_view();

?>
<!DOCTYPE html>
<html data-bs-theme="<?= esc_attr($view->string('html', 'light')); ?>">

<head>
   <meta charset="<?php bloginfo('charset'); ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <?php wp_head(); ?>
</head>

<body class="<?= esc_attr($view->css('body')); ?>">

   <header class="<?= esc_attr($view->css('header')); ?>">
      <?php $view->render('t_navbar'); ?>
      <?php $view->render('t_banner'); ?>
   </header>

   <div class="<?= esc_attr($view->css('div1')); ?>">
      <div class="<?= esc_attr($view->css('div2')); ?>">

         <aside class="<?= esc_attr($view->css('asideL')); ?>">
            <?php $view->render('t_asideL'); ?>
         </aside>

         <main class="<?= esc_attr($view->css('main')); ?>">

            <div class="<?= esc_attr($view->css('postheader')); ?>">
               <?php $view->render('t_postheader'); ?>
            </div>

            <article class="<?= esc_attr($view->css('article')); ?>">

               <?php if (have_posts()): ?>

                  <?php while (have_posts()): ?>
                     <?php the_post(); ?>

                     <?php $view->render('t_main'); ?>

                     <?php if ($view->bool('show_content')): ?>
                        <?php the_content(); ?>
                     <?php endif; ?>

                     <?php
                     if (
                        $view->bool('comentarios')
                        && (
                           comments_open()
                           || get_comments_number()
                        )
                     ) {
                        comments_template();
                     }
                     ?>

                  <?php endwhile; ?>

               <?php else: ?>

                  <?php $view->render('t_none'); ?>

               <?php endif; ?>

            </article>

            <div class="<?= esc_attr($view->css('postfooter')); ?>">
               <?php $view->render('t_postfooter'); ?>
            </div>

            <?php if ($view->bool('paginacion')): ?>

               <div class="<?= esc_attr($view->css('pagination')); ?>">
                  <small>
                     <?php
                     the_posts_pagination([
                        'prev_text' => '&laquo; Anterior',
                        'mid_size' => 0,
                        'next_text' => 'Siguiente &raquo;',
                     ]);
                     ?>
                  </small>
               </div>

            <?php endif; ?>

         </main>

         <aside class="<?= esc_attr($view->css('asideR')); ?>">
            <?php $view->render('t_asideR'); ?>
         </aside>

      </div>
   </div>

   <footer class="<?= esc_attr($view->css('footer')); ?>">
      <?php $view->render('t_footer'); ?>
   </footer>

   <?php wp_footer(); ?>

</body>

</html>
