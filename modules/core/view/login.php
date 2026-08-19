<?php
declare(strict_types=1);

use FWK\Modules\Core\Services\AuthService;

if (!defined('ABSPATH')) {
   exit;
}

$auth = new AuthService();

if ($auth->is_authenticated()) {
   wp_safe_redirect(
      home_url(
         $auth->get_authenticated_home()
      )
   );

   exit;
}

$loginError = $auth->handle_login();
$loginUser = $auth->get_submitted_user();
$rememberChecked = $auth->is_remember_requested();

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
         <?php if ($loginError !== ''): ?>

            <div class="alert alert-danger">
               <?= esc_html($loginError); ?>
            </div>

         <?php endif; ?>
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
                  value="<?= esc_attr($loginUser); ?>" required>

            </div>

            <div class="mb-3">

               <label for="fwk-login-password" class="form-label">
                  <?= esc_html__('Contraseña', 'FWK'); ?>
               </label>

               <input type="password" class="form-control" id="fwk-login-password" name="user_password"
                  autocomplete="current-password" required>

            </div>
            <div class="form-check mb-3">

               <input class="form-check-input" type="checkbox" value="1" id="fwk-login-remember" name="remember"
                  <?= checked($rememberChecked, true, false); ?>>

               <label class="form-check-label" for="fwk-login-remember">
                  <?= esc_html__('Recordarme', 'FWK'); ?>
               </label>

            </div>

            <div class="d-grid">

               <button type="submit" class="btn btn-primary">
                  <?= esc_html__('Ingresar', 'FWK'); ?>
               </button>

            </div>

         </form>
         <div class="mt-3 text-center">

            <a href="<?= esc_url(
               wp_lostpassword_url(
                  home_url('/login')
               )
            ); ?>">
               <?= esc_html__('¿Olvidó su contraseña?', 'FWK'); ?>
            </a>
            <div class="mt-2 text-center">

               <a href="<?= esc_url(home_url('/solicitar-ingreso')); ?>">
                  <?= esc_html__('Solicitar ingreso', 'FWK'); ?>
               </a>

            </div>
         </div>

      </div>

   </div>

</section>
