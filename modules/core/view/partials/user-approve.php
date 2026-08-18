<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

?>

<form method="post" class="d-inline">

   <?php
   wp_nonce_field(
      'fwk_activate_user',
      'fwk_activate_user_nonce'
   );
   ?>

   <input type="hidden" name="user_id" value="<?= esc_attr(
      (string) ($args['user_id'] ?? 0)
   ); ?>">

   <button type="submit" class="btn btn-success btn-sm">
      <?= esc_html__(
         'Aprobar',
         'FWK'
      ); ?>
   </button>

</form>
