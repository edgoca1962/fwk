<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
   exit;
}

return [
   /*
    * Clases estructurales.
    */
   'html' => 'dark',
   'body' => 'lang-es',
   'header' => 'mb-5',
   'div1' => 'container',
   'div2' => 'row pt-5',
   'asideL' => '',
   'main' => 'col-md-9',
   'postheader' => '',
   'article' => '',
   'pagination' => 'mb-5',
   'postfooter' => '',
   'asideR' => 'col-md-3',
   'footer' => 'container',

   /*
    * Visibilidad y comportamiento.
    */
   'paginacion' => true,
   'comentarios' => false,
   'show_content' => false,

   /*
    * Templates globales.
    */
   't_navbar' => 'modules/core/view/navbar',
   't_banner' => 'modules/core/view/banner',
   't_asideL' => '',
   't_postheader' => '',
   't_main' => 'modules/core/view/content',
   't_postfooter' => '',
   't_none' => 'modules/core/view/none',
   't_asideR' => '',
   't_comments' => '',
   't_footer' => 'modules/core/view/footer',
   't_btn_cita' => '',

   /*
    * Encabezados y contenido.
    */
   'titulo' => '',
   'titulo_class' => 'fs-1 fw-bolder',
   'subtitulo' => '',
   'subtitulo_class' => 'fs-3 fw-bolder',
   'subtitulo2' => '',
   'subtitulo2_class' => 'fs-5 fw-bolder',

   /*
    * Banner.
    */
   'height' => '50dvh',
   'fontweight' => 'fw-lighter',
   'display' => 'display-3',
   'displaysub' => 'display-4',
   'displaysub2' => 'display-5',

   /*
    * Búsqueda.
    */
   'frmplaceholder' => 'Buscar',
   'search' => '',
   'msgsearch' => '',

   /*
    * Navegación.
    */
   'navbar_principal' => '',
   'link_parametros' => '',
   'link_regresar' => '',

   /*
    * Contexto de la solicitud.
    */
   'modulo' => '',
   'postType' => '',
   'request_type' => '',
   'page_slug' => '',
   'post_slug' => '',
   'taxonomy' => '',
   'term_slug' => '',
   'paged' => 1,


   /*
    * Datos del usuario.
    */
   'user_id' => 0,
   'usr_email' => '',
   'usr_f_name' => '',
   'usr_l_name' => '',
   'nombre' => '',
   'usr_login' => '',
   'whatsapp' => '',
   'user_masivo' => '',

   /*
    * Permisos visuales.
    */
   'admin' => false,
   'post_abc' => '',
   'post_status' => '',
   'post_view' => false,

   /*
    * Configuraciones adicionales.
    */
   'paypal_monto' => 50000,
   'paypal_descripcion' => 'Membresía Premium',
   'paypal_item' => 'Membresía Anual',
   'roles_prefijo' => 'user',
];