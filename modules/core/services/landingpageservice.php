<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Services;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Prepara los datos necesarios
 * para renderizar la Landing Page.
 */
final class LandingPageService
{
   /**
    * Prepara los datos de la página.
    *
    * @return array<string, mixed>
    */
   public function prepare_page(): array
   {
      return [
         'hero' =>
            $this->prepare_hero(),
      ];
   }

   /**
    * Prepara los datos del Hero.
    *
    * @return array<string, string>
    */
   private function prepare_hero(): array
   {
      return [
         'title' =>
            __('Gobernanza. Eficiencia.', 'FWK'),

         'highlight' =>
            __('Crecimiento', 'FWK'),

         'text' =>
            __(
               'En un entorno donde el volumen de datos y la velocidad del negocio superan con creces las capacidades de los sistemas tradicionales, la gobernanza financiera deja de ser una opción de cumplimiento para convertirse en un imperativo de supervivencia.',
               'FWK'
            ),

         'button_text' =>
            __('Contactar', 'FWK'),

         'button_url' =>
            '#contacto',

         'image_url' =>
            get_template_directory_uri()
            . '/assets/img/core/fgh-consulting-header.png',

         'image_alt' =>
            __('FGH Consulting', 'FWK'),
      ];
   }
}