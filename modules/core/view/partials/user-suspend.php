<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

?>

<form method="post" class="d-inline">

   <?php
   wp_nonce_field(
      'fwk_suspend_user',
      'fwk_suspend_user_nonce'
   );
   ?>

   <input type="hidden" name="user_id" value="<?= esc_attr(
      (string) ($args['user_id'] ?? 0)
   ); ?>">

   <button type="submit" class="btn btn-secondary btn-sm">
      <?= esc_html__(
         'Suspender',
         'FWK'
      ); ?>
   </button>

</form>
