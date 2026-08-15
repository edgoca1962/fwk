<?php

declare(strict_types=1);

use FWK\Modules\Core\Services\UserRequestService;

if (!defined('ABSPATH')) {
   exit;
}

$requestService = new UserRequestService();
$requestResult = $requestService->handle_request();
$errors = $requestResult['errors'];
$requestSuccess = $requestResult['success'];
$values = $requestResult['values'];

?>

<section class="container py-5">

   <div class="row justify-content-center">

      <div class="col-12 col-md-8 col-lg-6">

         <h1 class="mb-4 text-center">
            <?= esc_html__(
               'Solicitar ingreso',
               'FWK'
            ); ?>
         </h1>

         <?php if ($requestSuccess): ?>

            <div class="alert alert-success">
               <?= esc_html__(
                  'La solicitud fue enviada correctamente y queda pendiente de aprobación.',
                  'FWK'
               ); ?>
            </div>

         <?php endif; ?>

         <?php if ($errors !== []): ?>

            <div class="alert alert-danger">

               <ul class="mb-0">

                  <?php foreach ($errors as $error): ?>

                     <li>
                        <?= esc_html($error); ?>
                     </li>

                  <?php endforeach; ?>

               </ul>

            </div>

         <?php endif; ?>

         <?php if (!$requestSuccess): ?>

            <form id="fwk-request-access-form" method="post">

               <?php
               wp_nonce_field(
                  'fwk_request_access_action',
                  'fwk_request_access_nonce'
               );
               ?>

               <div class="mb-3">

                  <label for="fwk-request-first-name" class="form-label">
                     <?= esc_html__(
                        'Nombre',
                        'FWK'
                     ); ?>
                  </label>

                  <input type="text" class="form-control" id="fwk-request-first-name" name="first_name"
                     autocomplete="given-name" value="<?= esc_attr($values['first_name']); ?>" required>

               </div>

               <div class="mb-3">

                  <label for="fwk-request-last-name" class="form-label">
                     <?= esc_html__(
                        'Apellidos',
                        'FWK'
                     ); ?>
                  </label>

                  <input type="text" class="form-control" id="fwk-request-last-name" name="last_name"
                     autocomplete="family-name" value="<?= esc_attr($values['last_name']); ?>" required>

               </div>

               <div class="mb-3">

                  <label for="fwk-request-email" class="form-label">
                     <?= esc_html__(
                        'Correo electrónico',
                        'FWK'
                     ); ?>
                  </label>

                  <input type="email" class="form-control" id="fwk-request-email" name="user_email" autocomplete="email"
                     value="<?= esc_attr($values['user_email']); ?>" required>

               </div>

               <div class="d-grid">

                  <button type="submit" class="btn btn-primary">
                     <?= esc_html__(
                        'Enviar solicitud',
                        'FWK'
                     ); ?>
                  </button>

               </div>

            </form>
         <?php endif; ?>

      </div>

   </div>

</section>
