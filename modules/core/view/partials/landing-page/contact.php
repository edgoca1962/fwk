<?php

declare(strict_types=1);

use FWK\Modules\Core\Services\ContactService;


if (!defined('ABSPATH')) {
   exit;
}

$contactService =
   new ContactService();

$result =
   $contactService->process();

$contact =
   $args['contact']
   ?? [];

$fields =
   $contact['fields']
   ?? [];

$button =
   $contact['button']
   ?? [];

$decoration =
   $contact['decoration']
   ?? [];

?>

<section id="contacto">

   <div class="
         text-primary
         overflow-hidden
         position-relative
      ">

      <?php if (
         ($decoration['image_url'] ?? '')
         !== ''
      ): ?>

         <div class="
               position-absolute
               start-0
               top-50
               translate-middle-y
               z-1
               d-none
               d-lg-block
               d-xxl-none
            " style="
               width: 40%;
               pointer-events: none;
            ">

            <img src="<?= esc_url(
               $decoration['image_url']
            ); ?>" alt="<?= esc_attr(
                $decoration['image_alt']
                ?? ''
             ); ?>" class="z-1" style="
                  transform: translateX(-50%);
                  max-width: 400px;
               ">

         </div>

      <?php endif; ?>

      <div class="container-xxl">

         <div class="
               col-lg-10
               col-xxl-12
               ms-auto
               position-relative
               z-3
               p-5
            ">
            <?php if (($result['message'] ?? '') !== ''): ?>

               <div class="
         alert
         <?= !empty($result['success'])
            ? 'alert-success'
            : 'alert-danger'; ?>
         mb-4
      " role="alert">
                  <?= esc_html(
                     $result['message']
                  ); ?>
               </div>

            <?php endif; ?>

            <form id="mensaje_contacto" method="post" action="<?= esc_url(
               home_url('/')
            ); ?>#contacto" class="
            needs-validation
            bg-dark
            text-bg-dark
            border
            rounded-5
            p-4" novalidate>

               <?php $contactService->render_nonce(); ?>

               <h2 class="
                     text-center
                     display-6
                     mb-5
                  ">
                  <?= esc_html(
                     $contact['title']
                     ?? ''
                  ); ?>
               </h2>

               <div class="font-second">

                  <div class="row">

                     <?php foreach (
                        ['name', 'whatsapp', 'email']
                        as $fieldKey
                     ): ?>

                        <?php
                        $field =
                           $fields[$fieldKey]
                           ?? [];

                        $fieldName =
                           (string) (
                              $field['name']
                              ?? ''
                           );

                        $fieldType =
                           (string) (
                              $field['type']
                              ?? 'text'
                           );

                        $required =
                           (bool) (
                              $field['required']
                              ?? false
                           );
                        ?>

                        <div class="
                              col-lg-4
                              mb-3
                           ">

                           <label for="<?= esc_attr(
                              $fieldName
                           ); ?>" class="form-label">
                              <?= esc_html(
                                 $field['label']
                                 ?? ''
                              ); ?>
                           </label>

                           <input id="<?= esc_attr(
                              $fieldName
                           ); ?>" name="<?= esc_attr(
                               $fieldName
                            ); ?>" type="<?= esc_attr(
                                $fieldType
                             ); ?>" class="
                                 form-control
                                 border
                              " <?= $required
                                 ? 'required'
                                 : ''; ?>>

                           <div class="invalid-feedback">
                              <?= esc_html(
                                 $field['invalid_message']
                                 ?? ''
                              ); ?>
                           </div>

                        </div>

                     <?php endforeach; ?>

                  </div>

                  <?php
                  $message =
                     $fields['message']
                     ?? [];
                  ?>

                  <div class="row mb-3">

                     <div class="col">

                        <label for="<?= esc_attr(
                           $message['name']
                           ?? 'mensaje'
                        ); ?>" class="form-label">
                           <?= esc_html(
                              $message['label']
                              ?? ''
                           ); ?>
                        </label>

                        <textarea id="<?= esc_attr(
                           $message['name']
                           ?? 'mensaje'
                        ); ?>" name="<?= esc_attr(
                            $message['name']
                            ?? 'mensaje'
                         ); ?>" class="
                              form-control
                              border
                           " rows="5" <?= !empty(
                              $message['required']
                           )
                              ? 'required'
                              : ''; ?>></textarea>

                        <div class="invalid-feedback">
                           <?= esc_html(
                              $message['invalid_message']
                              ?? ''
                           ); ?>
                        </div>

                     </div>

                  </div>

                  <div class="row mt-3">

                     <div class="
                           col
                           text-center
                           mb-3
                        ">

                        <button type="submit" name="enviar_mensaje" class="
                              btn
                              btn-primary
                              font-second
                              text-dark
                              fw-bold
                           ">

                           <?php if (
                              ($button['icon'] ?? '')
                              !== ''
                           ): ?>

                              <span class="me-1">
                                 <i class="
                                       bi
                                       <?= esc_attr(
                                          $button['icon']
                                       ); ?>
                                    "></i>
                              </span>

                           <?php endif; ?>

                           <?= esc_html(
                              $button['text']
                              ?? ''
                           ); ?>

                        </button>

                     </div>

                  </div>

               </div>

            </form>

         </div>

      </div>

   </div>

</section>
