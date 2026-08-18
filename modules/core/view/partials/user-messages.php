<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$messages = $args['messages'] ?? [];

?>

<?php foreach ($messages as $message): ?>

   <div class="alert <?= esc_attr(
      $message['class']
   ); ?>">

         <?= esc_html(
            $message['message']
         ); ?>

         <?php if ($message['url'] !== ''): ?>

         <div class="mt-2">

            <a href="<?= esc_url(
               $message['url']
            ); ?>" target="_blank" rel="noopener">
                     <?= esc_html__(
                        'Abrir enlace de restablecimiento',
                        'FWK'
                     ); ?>
            </a>

         </div>

         <?php endif; ?>

   </div>

<?php endforeach; ?>
