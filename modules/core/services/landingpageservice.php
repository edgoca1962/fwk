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

         'partners' =>
            $this->prepare_partners(),

         'services' =>
            $this->prepare_services(),

         'strategy' =>
            $this->prepare_strategy(),

         'integrations' =>
            $this->prepare_integrations(),

         'governance' =>
            $this->prepare_governance(),

         'coaching' =>
            $this->prepare_coaching(),

         'experience' =>
            $this->prepare_experience(),

         'contact' =>
            $this->prepare_contact(),
      ];
   }
   /**
    * Devuelve las clases visuales asociadas
    * a una variante de sección.
    *
    * @return array{
    *    background: string,
    *    text: string
    * }
    */
   public function get_section_variant(
      string $variant = ''
   ): array {
      return match ($variant) {

         'orange' => [
            'background' => 'bg-primary',
            'text' => 'text-dark',
         ],

         'white' => [
            'background' => 'bg-white',
            'text' => 'text-dark',
         ],

         'beige' => [
            'background' => 'bg-beige',
            'text' => 'text-dark',
         ],

         default => [
            'background' => '',
            'text' => '',
         ],
      };
   }

   /**
    * Prepara los datos del Hero.
    *
    * @return array<string, string>
    */
   private function prepare_hero(): array
   {
      return [
         'title1' =>
            __('Planificación Estretégica-', 'FWK'),

         'title2' =>
            __('Gobernanza-Eficiencia-', 'FWK'),

         'title3' =>
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
   /**
    * Prepara las marcas asociadas.
    *
    * @return array<int, array<string, string>>
    */
   private function prepare_partners(): array
   {
      $imagePath =
         get_template_directory_uri()
         . '/assets/img/core/';

      return [
         'section_classes' => $this->get_section_variant('beige'),
         'title' => 'Advisory:',
         'items' => [
            [
               'name' => 'Simetrik',
               'url' =>
                  'https://www.simetrik.com/',
               'image_url' =>
                  $imagePath
                  . 'simetrik02.png',
            ],

            [
               'name' => 'ICGConnect',
               'url' =>
                  'https://www.icgconnect.com/',
               'image_url' =>
                  $imagePath
                  . 'icgconnect00.png',
            ],

            [
               'name' => 'Analytics',
               'url' =>
                  '#',
               'image_url' =>
                  $imagePath
                  . 'analitycs.png',
            ],
         ],
      ];
   }

   /**
    * Prepara la sección de servicios.
    *
    * @return array{
    *    section_classes: array{
    *       background: string,
    *       text: string
    *    },
    *    items: array<int, array<string, string>>
    * }
    */
   private function prepare_services(): array
   {
      $imagePath =
         get_template_directory_uri()
         . '/assets/img/core/';

      return [
         'section_classes' =>
            $this->get_section_variant(
               'white'
            ),

         'items' => [
            [
               'title' =>
                  __(
                     'Gestión Estratégica Basada en Datos',
                     'FWK'
                  ),

               'image_url' =>
                  $imagePath
                  . 'bg-fgh-orange-lineas2.svg',

               'image_alt' =>
                  __(
                     'Gestión Estratégica',
                     'FWK'
                  ),

               'url' =>
                  '#strategy',

               'button_text' =>
                  __(
                     'Ver más...',
                     'FWK'
                  ),

               'variant' =>
                  'orange',
            ],

            [
               'title' =>
                  __(
                     'Gobernanza Estratégica Corporativa',
                     'FWK'
                  ),

               'image_url' =>
                  $imagePath
                  . 'bg-fgh-gray.svg',

               'image_alt' =>
                  __(
                     'Gobernanza Estratégica Corporativa',
                     'FWK'
                  ),

               'url' =>
                  '#gobernanza',

               'button_text' =>
                  __(
                     'Ver más...',
                     'FWK'
                  ),

               'variant' =>
                  'light',
            ],

            [
               'title' =>
                  __(
                     'Coaching Financiero',
                     'FWK'
                  ),

               'image_url' =>
                  $imagePath
                  . 'bg-fgh-orange-lineas2.svg',

               'image_alt' =>
                  __(
                     'Coaching Financiero',
                     'FWK'
                  ),

               'url' =>
                  '#coaching',

               'button_text' =>
                  __(
                     'Ver más...',
                     'FWK'
                  ),

               'variant' =>
                  'orange',
            ],

            [
               'title' =>
                  __(
                     'Acerca de mi experiencia',
                     'FWK'
                  ),

               'image_url' =>
                  $imagePath
                  . 'FGH-Consulting.png',

               'image_alt' =>
                  __(
                     'Acerca de mi experiencia',
                     'FWK'
                  ),

               'url' =>
                  '#acerca',

               'button_text' =>
                  __(
                     'Ver más...',
                     'FWK'
                  ),

               'variant' =>
                  'dark',
            ],
         ],
      ];
   }

   /**
    * Prepara la sección de Gestión Estratégica
    * Basada en Datos.
    *
    * @return array{
    *    section_classes: array{
    *       background: string,
    *       text: string
    *    },
    *    title: string,
    *    text: string,
    *    items: array<int, array{
    *       icon: string,
    *       text: string
    *    }>,
    *    button_text: string,
    *    button_url: string,
    *    image_url: string,
    *    image_alt: string,
    *    quote: array{
    *       text: string,
    *       author: string,
    *       section_classes: array{
    *          background: string,
    *          text: string
    *       }
    *    }
    * }
    */
   private function prepare_strategy(): array
   {
      return [
         'section_classes' =>
            $this->get_section_variant(
               'beige'
            ),

         'title' =>
            __(
               'Gestión Estratégica Basada en Datos',
               'FWK'
            ),

         'text' =>
            __(
               'Transforme datos en éxito tangible. Anticipe tendencias y optimice recursos mediante modelos predictivos para liderar con inteligencia y asegurar un crecimiento sostenible de la organización:',
               'FWK'
            ),

         'items' => [
            [
               'icon' =>
                  'bi-zoom-in',

               'text' =>
                  __(
                     'Auditoría de datos (ERP y Sistemas Satélites).',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-database-fill-gear',

               'text' =>
                  __(
                     'Diseño de la Planificación Estratégica basada en datos (Indicadores, Dashboards, etc.).',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-person-fill-gear',

               'text' =>
                  __(
                     'Gestión del cambio para la implementación de la transformación organizacional.',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-bullseye',

               'text' =>
                  __(
                     'Seguimiento del logro de los objetivos estratégicos organizacionales.',
                     'FWK'
                  ),
            ],
         ],

         'button_text' =>
            __(
               'Contactar',
               'FWK'
            ),

         'button_url' =>
            '#contacto',

         'image_url' =>
            get_template_directory_uri()
            . '/assets/img/core/businessAnalitycs.png',

         'image_alt' =>
            __(
               'Gestión Estratégica Basada en Datos',
               'FWK'
            ),

         'quote' => [
            'text' =>
               __(
                  'La planificación a largo plazo no es pensar en decisiones futuras, sino en el futuro de las decisiones presentes.',
                  'FWK'
               ),

            'author' =>
               'Peter Drucker',

            'section_classes' =>
               $this->get_section_variant(
                  'orange'
               ),
         ],
      ];
   }

   /**
    * Prepara la sección de integraciones.
    *
    * @return array{
    *    title: string,
    *    items: array<int, array{
    *       name: string,
    *       url: string,
    *       image_url: string
    *    }>
    * }
    */
   private function prepare_integrations(): array
   {
      $imagePath =
         get_template_directory_uri()
         . '/assets/img/core/marcas/';

      return [
         'title' =>
            __(
               'Integraciones',
               'FWK'
            ),

         'items' => [
            [
               'name' =>
                  'Dolibarr',

               'url' =>
                  'https://www.dolibarr.org/',

               'image_url' =>
                  $imagePath
                  . 'dolibarr.svg',
            ],

            [
               'name' =>
                  'ERPNext',

               'url' =>
                  'https://www.smartbiterp.com/cr/',

               'image_url' =>
                  $imagePath
                  . 'ERPNext.png',
            ],

            [
               'name' =>
                  'erpAG',

               'url' =>
                  'https://www.erpag.com/',

               'image_url' =>
                  $imagePath
                  . 'erpAG.png',
            ],

            [
               'name' =>
                  'Odoo',

               'url' =>
                  'https://www.odoo.com/es',

               'image_url' =>
                  $imagePath
                  . 'odoo.png',
            ],

            [
               'name' =>
                  'Sage',

               'url' =>
                  'https://www.sage.com/es-es/',

               'image_url' =>
                  $imagePath
                  . 'sage.svg',
            ],

            [
               'name' =>
                  'Orisha',

               'url' =>
                  'https://commerce.orisha.com/',

               'image_url' =>
                  $imagePath
                  . 'orisha.png',
            ],

            [
               'name' =>
                  'SAP',

               'url' =>
                  'https://www.sap.com/latinamerica/index.html',

               'image_url' =>
                  $imagePath
                  . 'sap.svg',
            ],

            [
               'name' =>
                  'Microsoft Dynamics 365',

               'url' =>
                  'https://www.microsoft.com/es-es/dynamics-365/products/business-central',

               'image_url' =>
                  $imagePath
                  . 'microsoft.png',
            ],

            [
               'name' =>
                  'Oracle NetSuite',

               'url' =>
                  'https://www.netsuite.com/portal/home.shtml',

               'image_url' =>
                  $imagePath
                  . 'oracle.svg',
            ],

            [
               'name' =>
                  'Zebra BI',

               'url' =>
                  'https://zebrabi.com/',

               'image_url' =>
                  $imagePath
                  . 'zebra.webp',
            ],

            [
               'name' =>
                  'Claude',

               'url' =>
                  '#',

               'image_url' =>
                  $imagePath
                  . 'claude.png',
            ],
         ],
      ];
   }

   /**
    * Prepara la sección de Gobernanza
    * Estratégica Corporativa.
    *
    * @return array<string, mixed>
    */
   private function prepare_governance(): array
   {
      return [
         'section_classes' =>
            $this->get_section_variant(
               'beige'
            ),

         'title' =>
            __(
               'Gobernanza Estratégica Corporativa',
               'FWK'
            ),

         'text' =>
            __(
               'Abordamos la brecha entre el crecimiento operativo y el control financiero mediante un proceso de intervención de extremo a extremo:',
               'FWK'
            ),

         'items' => [
            [
               'icon' =>
                  'bi-1-circle',

               'text' =>
                  __(
                     'Diagnóstico de Madurez y Brechas (Evaluación).',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-2-circle',

               'text' =>
                  __(
                     'Diseño de la Arquitectura de Control (Estrategia).',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-3-circle',

               'text' =>
                  __(
                     'Implementación y Automatización Tecnológica (Ejecución).',
                     'FWK'
                  ),
            ],
         ],

         'button_text' =>
            __(
               'Contactar',
               'FWK'
            ),

         'button_url' =>
            '#contacto',

         'image_url' =>
            get_template_directory_uri()
            . '/assets/img/core/fgh-consulting-header.png',

         'image_alt' =>
            __(
               'Gobernanza Estratégica Corporativa',
               'FWK'
            ),

         'quote' => [
            'section_classes' =>
               $this->get_section_variant(
                  'orange'
               ),

            'text' =>
               __(
                  'La eficiencia estratégica no se logra solo con tecnología, sino con la arquitectura de control adecuada para gobernarla.',
                  'FWK'
               ),
         ],
      ];
   }
   /**
    * Prepara la sección de Coaching Financiero.
    *
    * @return array<string, mixed>
    */
   private function prepare_coaching(): array
   {
      return [
         'section_classes' =>
            $this->get_section_variant(
               'beige'
            ),

         'title' =>
            __(
               'Coaching Financiero',
               'FWK'
            ),

         'text' =>
            __(
               'Bajo la premisa de que la solidez de un patrimonio es el reflejo de la solidez del carácter, este programa de coaching está dirigido a corporaciones que buscan potenciar el bienestar y la efectividad de su capital humano. No nos limitamos a transformar balances; transformamos el ser. Cultivamos la disciplina, la visión y la integridad necesarias para que el éxito financiero sea una consecuencia natural de hábitos de alta efectividad.',
               'FWK'
            ),

         'items' => [
            [
               'icon' =>
                  'bi-compass',

               'text' =>
                  __(
                     'Diagnóstico y Hoja de Ruta.',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-gear',

               'text' =>
                  __(
                     'Reingeniería de Mentalidad y Sesgos.',
                     'FWK'
                  ),
            ],

            [
               'icon' =>
                  'bi-tools',

               'text' =>
                  __(
                     'Herramientas y Auditoría de Progreso.',
                     'FWK'
                  ),

               'links' => [
                  [
                     'label' =>
                        __(
                           'Sistema Gestión Financiera',
                           'FWK'
                        ),

                     'url' =>
                        'https://sgf.fgh-org.org',
                  ],

                  [
                     'label' =>
                        __(
                           'Sistema de Citas',
                           'FWK'
                        ),

                     'url' =>
                        'https://calendly.com/edgoca1962/coachingfinanciero',
                  ],
               ],
            ],
         ],

         'button_text' =>
            __(
               'Contactar',
               'FWK'
            ),

         'button_url' =>
            '#contacto',

         'image_url' =>
            get_template_directory_uri()
            . '/assets/img/core/FGH-Consulting-2.png',

         'image_alt' =>
            __(
               'Coaching Financiero',
               'FWK'
            ),

         'quote' => [
            'section_classes' =>
               $this->get_section_variant(
                  'orange'
               ),

            'text' =>
               __(
                  'Nunca un mayor ingreso ha sido la solución a la causa raíz de los problemas financieros personales.',
                  'FWK'
               ),
         ],
      ];
   }
   /**
    * Prepara la sección de experiencia.
    *
    * @return array<string, mixed>
    */
   private function prepare_experience(): array
   {
      return [
         'section_classes' =>
            $this->get_section_variant(
               'white'
            ),

         'title' =>
            __(
               'Acerca de mi experiencia',
               'FWK'
            ),

         'text' =>
            __(
               'Con una trayectoria de más de 30 años en la intersección de las finanzas, la tecnología y el desarrollo humano, me he especializado en robustecer estructuras operativas mediante la automatización y el cumplimiento regulatorio. Su enfoque único combina el rigor técnico de la alta dirección bancaria con una profunda vocación por el desarrollo del potencial humano, creyendo firmemente que tanto la excelencia empresarial como la prosperidad personal emanan de una visión clara y un liderazgo ético.',
               'FWK'
            ),

         'linkedin' => [
            'label' =>
               'Edwin González',

            'url' =>
               'https://www.linkedin.com/in/edwin-gonz%C3%A1lez-1756236/',

            'icon' =>
               'bi-linkedin',
         ],
      ];
   }

   /**
    * Prepara la sección de contacto.
    *
    * @return array<string, mixed>
    */
   private function prepare_contact(): array
   {
      return [
         'title' =>
            __(
               'Contactar',
               'FWK'
            ),

         'fields' => [
            'name' => [
               'label' =>
                  __(
                     'Nombre Completo',
                     'FWK'
                  ),

               'name' =>
                  'nombre',

               'type' =>
                  'text',

               'required' =>
                  true,

               'invalid_message' =>
                  __(
                     'Por favor, incluir el nombre.',
                     'FWK'
                  ),
            ],

            'whatsapp' => [
               'label' =>
                  __(
                     'WhatsApp',
                     'FWK'
                  ),

               'name' =>
                  'whatsapp',

               'type' =>
                  'tel',

               'required' =>
                  true,

               'invalid_message' =>
                  __(
                     'Por favor, incluir un móvil para WhatsApp.',
                     'FWK'
                  ),
            ],

            'email' => [
               'label' =>
                  __(
                     'Email',
                     'FWK'
                  ),

               'name' =>
                  'email',

               'type' =>
                  'email',

               'required' =>
                  true,

               'invalid_message' =>
                  __(
                     'Por favor, incluir un email.',
                     'FWK'
                  ),
            ],

            'message' => [
               'label' =>
                  __(
                     'Mensaje',
                     'FWK'
                  ),

               'name' =>
                  'mensaje',

               'type' =>
                  'textarea',

               'required' =>
                  true,

               'invalid_message' =>
                  __(
                     'Por favor, incluir un mensaje.',
                     'FWK'
                  ),
            ],
         ],

         'button' => [
            'text' =>
               __(
                  'Enviar mensaje',
                  'FWK'
               ),

            'icon' =>
               'bi-envelope-check-fill',
         ],

         'decoration' => [
            'image_url' =>
               get_template_directory_uri()
               . '/assets/img/core/outline-white.svg',

            'image_alt' =>
               __(
                  'Logo Contorno Blanco',
                  'FWK'
               ),
         ],
      ];
   }
}