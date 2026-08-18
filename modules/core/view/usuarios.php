<?php

declare(strict_types=1);

use FWK\Modules\Core\Services\UserService;

if (!defined('ABSPATH')) {
   exit;
}

$userService =
   new UserService();

$pageData =
   $userService->prepare_management_page();

$messages =
   $pageData['messages'];

$pendingUsers =
   $pageData['users']['pending'];

$activeUsers =
   $pageData['users']['active'];

$rejectedUsers =
   $pageData['users']['rejected'];

$suspendedUsers =
   $pageData['users']['suspended'];

$user = get_user_by('id', 18);
print_r($user->roles);
echo '<br>';

print_r(user_can(
   $user,
   'fwk_manage_users'
));
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

         <?php
         get_template_part(
            'modules/core/view/partials/user-messages',
            null,
            [
               'messages' => $messages,
            ]
         );
         ?>

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

                              <?php
                              get_template_part(
                                 'modules/core/view/partials/user-approve',
                                 null,
                                 [
                                    'user_id' => $user['id'],
                                 ]
                              );
                              ?>

                              <?php
                              get_template_part(
                                 'modules/core/view/partials/user-reject',
                                 null,
                                 [
                                    'user_id' => $user['id'],
                                 ]
                              );
                              ?>

                              <?php
                              get_template_part(
                                 'modules/core/view/partials/user-suspend',
                                 null,
                                 [
                                    'user_id' => $user['id'],
                                 ]
                              );
                              ?>

                           </td>

                        </tr>

                     <?php endforeach; ?>

                  </tbody>

               </table>

            </div>

         <?php endif; ?>

         <section class="mt-5">

            <h2 class="h4 mb-3">
               <?= esc_html__(
                  'Usuarios activos',
                  'FWK'
               ); ?>
            </h2>

            <?php
            get_template_part(
               'modules/core/view/partials/user-status-table',
               null,
               [
                  'users' =>
                     $activeUsers,

                  'empty_message' => __(
                     'No existen usuarios activos.',
                     'FWK'
                  ),
               ]
            );
            ?>

         </section>

         <section class="mt-5">

            <h2 class="h4 mb-3">
               <?= esc_html__(
                  'Usuarios rechazados',
                  'FWK'
               ); ?>
            </h2>

            <?php
            get_template_part(
               'modules/core/view/partials/user-status-table',
               null,
               [
                  'users' =>
                     $rejectedUsers,

                  'empty_message' => __(
                     'No existen usuarios rechazados.',
                     'FWK'
                  ),
               ]
            );
            ?>

         </section>

         <section class="mt-5">

            <h2 class="h4 mb-3">
               <?= esc_html__(
                  'Usuarios suspendidos',
                  'FWK'
               ); ?>
            </h2>

            <?php
            get_template_part(
               'modules/core/view/partials/user-status-table',
               null,
               [
                  'users' =>
                     $suspendedUsers,

                  'empty_message' => __(
                     'No existen usuarios suspendidos.',
                     'FWK'
                  ),
               ]
            );
            ?>

         </section>
      </div>

   </div>

</section>
