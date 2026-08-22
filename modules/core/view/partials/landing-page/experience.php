<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$experience =
   $args['experience']
   ?? [];

$sectionClasses =
   $experience['section_classes']
   ?? [];

$linkedin =
   $experience['linkedin']
   ?? [];

?>

<section id="acerca" class="
      <?= esc_attr(
         $sectionClasses['background']
         ?? ''
      ); ?>
      <?= esc_attr(
         $sectionClasses['text']
         ?? ''
      ); ?>
      py-5
   ">

   <div class="container autoShow">

      <h2 class="fs-2 mb-4">
         <?= esc_html(
            $experience['title']
            ?? ''
         ); ?>
      </h2>

      <p class="
            font-second
            fs-4
            mb-4
         ">
         <?= esc_html(
            $experience['text']
            ?? ''
         ); ?>
      </p>

      <?php if (
         ($linkedin['url'] ?? '')
         !== ''
      ): ?>

         <p class="fs-4 mb-0">

            <a href="<?= esc_url(
               $linkedin['url']
            ); ?>" class="text-reset text-decoration-none" target="_blank" rel="noopener">

               <i class="
                     bi
                     <?= esc_attr(
                        $linkedin['icon']
                        ?? ''
                     ); ?>
                     me-1
                  "></i>

               <?= esc_html(
                  $linkedin['label']
                  ?? ''
               ); ?>

            </a>

         </p>

      <?php endif; ?>

   </div>

</section>
