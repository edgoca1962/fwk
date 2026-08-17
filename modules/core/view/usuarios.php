<?php

declare(strict_types=1);

use FWK\Modules\Core\Services\UserService;

if (!defined('ABSPATH')) {
   exit;
}

$userService = new UserService();

if (!$userService->can_manage_users()) {
   wp_safe_redirect(
      home_url('/')
   );

   exit;
}

$activationResult = $userService->handle_activation();
$pendingUsers = $userService->get_management_data()['pending_users'];

?>

<section class="container py-5">

   <div class="row">

      <div class="col-12">

         <h1 class="mb-4">
            <?= esc_html__('Administración de usuarios', 'FWK'); ?>
         </h1>

         <h2 class="h4 mb-3">
            <?= esc_html__('Solicitudes pendientes', 'FWK'); ?>
         </h2>

         <?php if ($activationResult['message'] !== ''): ?>

            <div class="alert <?= $activationResult['success']
               ? 'alert-success'
               : 'alert-danger'; ?> alert-dismissible fade show">
               <?= esc_html(
                  $activationResult['message']
               ); ?>
            </div>

         <?php endif; ?>

         <?php if (
            $activationResult['success']
            && $activationResult['reset_url'] !== ''
         ): ?>

            <div class="alert alert-warning">

               <p class="mb-2">
                  <?= esc_html__(
                     'Enlace temporal para establecer contraseña:',
                     'FWK'
                  ); ?>
               </p>

               <a href="<?= esc_url(
                  $activationResult['reset_url']
               ); ?>" target="_blank" rel="noopener">
                  <?= esc_html__(
                     'Abrir enlace de restablecimiento',
                     'FWK'
                  ); ?>
               </a>

            </div>

         <?php endif; ?>


         <?php if ($pendingUsers === []): ?>

            <div class="alert alert-info">
               <?= esc_html__('No existen solicitudes pendientes.', 'FWK'); ?>
            </div>

         <?php else: ?>

            <div class="table-responsive">

               <table class="table table-striped align-middle">

                  <thead>
                     <tr>
                        <th>
                           <?= esc_html__('Nombre', 'FWK'); ?>
                        </th>

                        <th>
                           <?= esc_html__('Correo electrónico', 'FWK'); ?>
                        </th>

                        <th>
                           <?= esc_html__('Estado', 'FWK'); ?>
                        </th>
                        <th>
                           <?= esc_html__('Acción', 'FWK'); ?>
                        </th>
                     </tr>
                  </thead>

                  <tbody>

                     <?php foreach ($pendingUsers as $user): ?>

                        <tr>

                           <td>
                              <?= esc_html($user['name']); ?>
                           </td>

                           <td>
                              <?= esc_html($user['email']); ?>
                           </td>

                           <td>
                              <?= esc_html($user['status']); ?>
                           </td>
                           <td>

                              <form method="post">

                                 <?php
                                 wp_nonce_field(
                                    'fwk_activate_user',
                                    'fwk_activate_user_nonce'
                                 );
                                 ?>

                                 <input type="hidden" name="user_id" value="<?= esc_attr(
                                    (string) $user['id']
                                 ); ?>">

                                 <button type="submit" class="btn btn-success btn-sm">
                                    <?= esc_html__(
                                       'Aprobar',
                                       'FWK'
                                    ); ?>
                                 </button>

                              </form>

                           </td>

                        </tr>

                     <?php endforeach; ?>

                  </tbody>

               </table>

            </div>

         <?php endif; ?>

      </div>

   </div>

</section>
