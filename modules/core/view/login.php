<?php
declare(strict_types=1);

use FWK\Modules\Core\Services\AuthService;

if (!defined('ABSPATH')) {
   exit;
}

$auth = new AuthService();
$loginError = $auth->handle_login();

?>

<section class="container py-5">

   <div class="row justify-content-center">

      <div class="col-12 col-md-6 col-lg-4">

         <h1 class="mb-4 text-center">
            <?= esc_html__(
               'Ingresar',
               'FWK'
            ); ?>
         </h1>

         <form id="fwk-login-form" method="post">
            <?php
            wp_nonce_field(
               'fwk_login_action',
               'fwk_login_nonce'
            );
            ?>
            <div class="mb-3">

               <label for="fwk-login-user" class="form-label">
                  <?= esc_html__(
                     'Correo electrónico o usuario',
                     'FWK'
                  ); ?>
               </label>

               <input type="text" class="form-control" id="fwk-login-user" name="user_login" autocomplete="username"
                  required>

            </div>

            <div class="mb-3">

               <label for="fwk-login-password" class="form-label">
                  <?= esc_html__(
                     'Contraseña',
                     'FWK'
                  ); ?>
               </label>

               <input type="password" class="form-control" id="fwk-login-password" name="user_password"
                  autocomplete="current-password" required>

            </div>

            <div class="d-grid">

               <button type="submit" class="btn btn-primary">
                  <?= esc_html__(
                     'Ingresar',
                     'FWK'
                  ); ?>
               </button>

            </div>

         </form>

      </div>

   </div>

</section>
