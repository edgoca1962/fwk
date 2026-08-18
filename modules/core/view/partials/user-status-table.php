<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

$users = $args['users'] ?? [];
$emptyMessage = $args['empty_message'] ?? '';

?>

<?php if ($users === []): ?>

   <div class="alert alert-info">
         <?= esc_html($emptyMessage); ?>
   </div>

<?php else: ?>

   <div class="table-responsive">

      <table class="table table-striped align-middle">

         <thead>
            <tr>

               <th>
                     <?= esc_html__(
                        'Nombre',
                        'FWK'
                     ); ?>
               </th>

               <th>
                     <?= esc_html__(
                        'Correo electrónico',
                        'FWK'
                     ); ?>
               </th>

               <th>
                     <?= esc_html__(
                        'Estado',
                        'FWK'
                     ); ?>
               </th>

               <th>
                     <?= esc_html__(
                        'Acción',
                        'FWK'
                     ); ?>
               </th>

            </tr>
         </thead>

         <tbody>

               <?php foreach ($users as $user): ?>

               <tr>

                  <td>
                           <?= esc_html(
                              $user['name']
                           ); ?>
                  </td>

                  <td>
                           <?= esc_html(
                              $user['email']
                           ); ?>
                  </td>

                  <td>
                           <?= esc_html(
                              $user['status']
                           ); ?>
                  </td>

                  <td>

                           <?php
                           get_template_part(
                              'modules/core/view/partials/user-send-review',
                              null,
                              [
                                 'user_id' =>
                                    $user['id'],
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
