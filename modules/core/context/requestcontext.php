<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Context;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Representa el estado de la solicitud principal de WordPress.
 *
 * La clase captura una fotografía de la consulta actual y evita que
 * Core y los módulos dependan directamente de funciones condicionales,
 * variables globales o parámetros GET dispersos.
 *
 * No utiliza Singleton porque representa datos de una solicitud.
 *
 * @package FWK
 */
final class RequestContext
{
   /**
    * Tipo de contenido relacionado con la solicitud.
    */
   private string $postType;

   /**
    * ID del objeto consultado.
    */
   private int $queriedObjectId;

   /**
    * Slug de la página actual.
    */
   private string $pageSlug;

   /**
    * Slug del post actual.
    */
   private string $postSlug;

   /**
    * Taxonomía consultada.
    */
   private string $taxonomy;

   /**
    * Slug del término consultado.
    */
   private string $termSlug;

   /**
    * ID del término consultado.
    */
   private int $termId;

   /**
    * ID del autor consultado.
    */
   private int $authorId;

   /**
    * Texto de búsqueda.
    */
   private string $searchQuery;

   /**
    * Página actual de paginación.
    */
   private int $paged;

   /**
    * Módulo solicitado explícitamente mediante la URL.
    */
   private string $requestedModule;

   /**
    * Objeto consultado por WordPress.
    *
    * Puede ser WP_Post, WP_Term, WP_Post_Type, WP_User o null.
    */
   private mixed $queriedObject;

   /**
    * Indicadores de la consulta.
    *
    * @var array<string, bool>
    */
   private array $flags;

   /**
    * El contexto debe construirse mediante capture().
    *
    * @param array<string, bool> $flags
    */
   private function __construct(
      string $postType,
      int $queriedObjectId,
      string $pageSlug,
      string $postSlug,
      string $taxonomy,
      string $termSlug,
      int $termId,
      int $authorId,
      string $searchQuery,
      int $paged,
      string $requestedModule,
      mixed $queriedObject,
      array $flags
   ) {
      $this->postType = $postType;
      $this->queriedObjectId = $queriedObjectId;
      $this->pageSlug = $pageSlug;
      $this->postSlug = $postSlug;
      $this->taxonomy = $taxonomy;
      $this->termSlug = $termSlug;
      $this->termId = $termId;
      $this->authorId = $authorId;
      $this->searchQuery = $searchQuery;
      $this->paged = max(1, $paged);
      $this->requestedModule = $requestedModule;
      $this->queriedObject = $queriedObject;
      $this->flags = $flags;
   }

   /**
    * Captura el estado actual de la consulta principal.
    */
   public static function capture(): self
   {
      $queriedObject = get_queried_object();
      $queriedObjectId = (int) get_queried_object_id();

      $postType = self::resolve_post_type($queriedObject);
      $pageSlug = self::resolve_page_slug($queriedObject);
      $postSlug = self::resolve_post_slug($queriedObject);
      $taxonomy = self::resolve_taxonomy($queriedObject);
      $termSlug = self::resolve_term_slug($queriedObject);
      $termId = self::resolve_term_id($queriedObject);
      $authorId = self::resolve_author_id($queriedObject);
      $paged = self::resolve_paged();
      $requestedModule = self::resolve_requested_module();

      $flags = [
         'front_page' => is_front_page(),
         'home' => is_home(),
         'page' => is_page(),
         'single' => is_single(),
         'singular' => is_singular(),
         'archive' => is_archive(),
         'post_type_archive' => is_post_type_archive(),
         'taxonomy' => is_tax(),
         'category' => is_category(),
         'tag' => is_tag(),
         'author' => is_author(),
         'search' => is_search(),
         '404' => is_404(),
         'paged' => is_paged(),
         'attachment' => is_attachment(),
         'feed' => is_feed(),
         'preview' => is_preview(),
      ];

      /**
       * Permite modificar los datos capturados antes de crear
       * el objeto definitivo.
       *
       * @var array<string, mixed> $data
       */
      $data = apply_filters(
         'fwk_request_context_data',
         [
            'post_type' => $postType,
            'queried_object_id' => $queriedObjectId,
            'page_slug' => $pageSlug,
            'post_slug' => $postSlug,
            'taxonomy' => $taxonomy,
            'term_slug' => $termSlug,
            'term_id' => $termId,
            'author_id' => $authorId,
            'search_query' => get_search_query(false),
            'paged' => $paged,
            'requested_module' => $requestedModule,
            'queried_object' => $queriedObject,
            'flags' => $flags,
         ]
      );

      return new self(
         sanitize_key((string) ($data['post_type'] ?? '')),
         absint($data['queried_object_id'] ?? 0),
         sanitize_title((string) ($data['page_slug'] ?? '')),
         sanitize_title((string) ($data['post_slug'] ?? '')),
         sanitize_key((string) ($data['taxonomy'] ?? '')),
         sanitize_title((string) ($data['term_slug'] ?? '')),
         absint($data['term_id'] ?? 0),
         absint($data['author_id'] ?? 0),
         sanitize_text_field((string) ($data['search_query'] ?? '')),
         max(1, absint($data['paged'] ?? 1)),
         sanitize_key((string) ($data['requested_module'] ?? '')),
         $data['queried_object'] ?? null,
         is_array($data['flags'] ?? null)
         ? $data['flags']
         : $flags
      );
   }

   /**
    * Devuelve el post type asociado a la solicitud.
    */
   public function get_post_type(): string
   {
      return $this->postType;
   }

   /**
    * Devuelve el ID del objeto consultado.
    */
   public function get_queried_object_id(): int
   {
      return $this->queriedObjectId;
   }

   /**
    * Devuelve el slug de la página actual.
    */
   public function get_page_slug(): string
   {
      return $this->pageSlug;
   }

   /**
    * Devuelve el slug del post o CPT singular actual.
    */
   public function get_post_slug(): string
   {
      return $this->postSlug;
   }

   /**
    * Devuelve la taxonomía consultada.
    */
   public function get_taxonomy(): string
   {
      return $this->taxonomy;
   }

   /**
    * Devuelve el slug del término consultado.
    */
   public function get_term_slug(): string
   {
      return $this->termSlug;
   }

   /**
    * Devuelve el ID del término consultado.
    */
   public function get_term_id(): int
   {
      return $this->termId;
   }

   /**
    * Devuelve el ID del autor consultado.
    */
   public function get_author_id(): int
   {
      return $this->authorId;
   }

   /**
    * Devuelve el texto de búsqueda.
    */
   public function get_search_query(): string
   {
      return $this->searchQuery;
   }

   /**
    * Devuelve la página actual de paginación.
    */
   public function get_paged(): int
   {
      return $this->paged;
   }

   /**
    * Devuelve el módulo solicitado mediante ?modulo=.
    */
   public function get_requested_module(): string
   {
      return $this->requestedModule;
   }

   /**
    * Devuelve el objeto consultado por WordPress.
    */
   public function get_queried_object(): mixed
   {
      return $this->queriedObject;
   }

   /**
    * Comprueba un indicador de la solicitud.
    */
   public function flag(string $name): bool
   {
      return (bool) ($this->flags[$name] ?? false);
   }

   /**
    * Indica si se consulta la portada estática.
    */
   public function is_front_page(): bool
   {
      return $this->flag('front_page');
   }

   /**
    * Indica si se consulta la página asignada al blog.
    */
   public function is_home(): bool
   {
      return $this->flag('home');
   }

   /**
    * Indica si se consulta una página.
    *
    * Puede comprobar opcionalmente un slug concreto.
    */
   public function is_page(?string $slug = null): bool
   {
      if (!$this->flag('page')) {
         return false;
      }

      if ($slug === null || $slug === '') {
         return true;
      }

      return $this->pageSlug === sanitize_title($slug);
   }

   /**
    * Indica si se consulta contenido singular.
    *
    * Puede comprobar opcionalmente uno o varios post types.
    *
    * @param string|string[]|null $postTypes
    */
   public function is_singular(
      string|array|null $postTypes = null
   ): bool {
      if (!$this->flag('singular')) {
         return false;
      }

      if ($postTypes === null || $postTypes === []) {
         return true;
      }

      $postTypes = array_map(
         'sanitize_key',
         (array) $postTypes
      );

      return in_array(
         $this->postType,
         $postTypes,
         true
      );
   }

   /**
    * Indica si se consulta una entrada singular que no es página.
    */
   public function is_single(?string $postType = null): bool
   {
      if (!$this->flag('single')) {
         return false;
      }

      if ($postType === null || $postType === '') {
         return true;
      }

      return $this->postType === sanitize_key($postType);
   }

   /**
    * Indica si se consulta algún archivo.
    *
    * Opcionalmente comprueba un post type.
    */
   public function is_archive(?string $postType = null): bool
   {
      if (!$this->flag('archive')) {
         return false;
      }

      if ($postType === null || $postType === '') {
         return true;
      }

      return $this->postType === sanitize_key($postType);
   }

   /**
    * Indica si se consulta el archivo de un post type.
    */
   public function is_post_type_archive(
      ?string $postType = null
   ): bool {
      if (!$this->flag('post_type_archive')) {
         return false;
      }

      if ($postType === null || $postType === '') {
         return true;
      }

      return $this->postType === sanitize_key($postType);
   }

   /**
    * Indica si se consulta una taxonomía.
    *
    * Puede comprobar taxonomía y término.
    */
   public function is_taxonomy(
      ?string $taxonomy = null,
      ?string $termSlug = null
   ): bool {
      $isTermArchive = $this->flag('taxonomy')
         || $this->flag('category')
         || $this->flag('tag');

      if (!$isTermArchive) {
         return false;
      }

      if (
         $taxonomy !== null
         && $taxonomy !== ''
         && $this->taxonomy !== sanitize_key($taxonomy)
      ) {
         return false;
      }

      if (
         $termSlug !== null
         && $termSlug !== ''
         && $this->termSlug !== sanitize_title($termSlug)
      ) {
         return false;
      }

      return true;
   }

   /**
    * Indica si se ejecuta una búsqueda.
    */
   public function is_search(): bool
   {
      return $this->flag('search');
   }

   /**
    * Indica si WordPress resolvió una página 404.
    */
   public function is_404(): bool
   {
      return $this->flag('404');
   }

   /**
    * Indica si la consulta está paginada.
    */
   public function is_paged(): bool
   {
      return $this->flag('paged');
   }

   /**
    * Indica si se consulta un archivo de autor.
    */
   public function is_author(): bool
   {
      return $this->flag('author');
   }

   /**
    * Devuelve una clasificación general de la solicitud.
    */
   public function get_type(): string
   {
      if ($this->is_404()) {
         return '404';
      }

      if ($this->is_search()) {
         return 'search';
      }

      if ($this->is_front_page()) {
         return 'front_page';
      }

      if ($this->is_home()) {
         return 'home';
      }

      if ($this->is_page()) {
         return 'page';
      }

      if ($this->is_singular()) {
         return 'singular';
      }

      if ($this->is_taxonomy()) {
         return 'taxonomy';
      }

      if ($this->is_author()) {
         return 'author';
      }

      if ($this->is_post_type_archive()) {
         return 'post_type_archive';
      }

      if ($this->is_archive()) {
         return 'archive';
      }

      return 'unknown';
   }

   /**
    * Devuelve los datos principales para depuración.
    *
    * @return array<string, mixed>
    */
   public function to_array(): array
   {
      return [
         'type' => $this->get_type(),
         'post_type' => $this->postType,
         'queried_object_id' => $this->queriedObjectId,
         'page_slug' => $this->pageSlug,
         'post_slug' => $this->postSlug,
         'taxonomy' => $this->taxonomy,
         'term_slug' => $this->termSlug,
         'term_id' => $this->termId,
         'author_id' => $this->authorId,
         'search_query' => $this->searchQuery,
         'paged' => $this->paged,
         'requested_module' => $this->requestedModule,
         'flags' => $this->flags,
      ];
   }

   /**
    * Determina el post type de la consulta.
    */
   private static function resolve_post_type(
      mixed $queriedObject
   ): string {
      if ($queriedObject instanceof \WP_Post) {
         return sanitize_key($queriedObject->post_type);
      }

      if ($queriedObject instanceof \WP_Post_Type) {
         return sanitize_key($queriedObject->name);
      }

      $postType = get_post_type();

      if (is_string($postType)) {
         return sanitize_key($postType);
      }

      $queryPostType = get_query_var('post_type');

      if (is_array($queryPostType)) {
         $queryPostType = reset($queryPostType);
      }

      return is_string($queryPostType)
         ? sanitize_key($queryPostType)
         : '';
   }

   /**
    * Determina el slug de la página.
    */
   private static function resolve_page_slug(
      mixed $queriedObject
   ): string {
      if (
         is_page()
         && $queriedObject instanceof \WP_Post
      ) {
         return sanitize_title(
            $queriedObject->post_name
         );
      }

      return '';
   }

   /**
    * Determina el slug del contenido singular.
    */
   private static function resolve_post_slug(
      mixed $queriedObject
   ): string {
      if ($queriedObject instanceof \WP_Post) {
         return sanitize_title(
            $queriedObject->post_name
         );
      }

      return '';
   }

   /**
    * Determina la taxonomía consultada.
    */
   private static function resolve_taxonomy(
      mixed $queriedObject
   ): string {
      if ($queriedObject instanceof \WP_Term) {
         return sanitize_key(
            $queriedObject->taxonomy
         );
      }

      return '';
   }

   /**
    * Determina el slug del término consultado.
    */
   private static function resolve_term_slug(
      mixed $queriedObject
   ): string {
      if ($queriedObject instanceof \WP_Term) {
         return sanitize_title(
            $queriedObject->slug
         );
      }

      return '';
   }

   /**
    * Determina el ID del término consultado.
    */
   private static function resolve_term_id(
      mixed $queriedObject
   ): int {
      if ($queriedObject instanceof \WP_Term) {
         return absint(
            $queriedObject->term_id
         );
      }

      return 0;
   }

   /**
    * Determina el ID del autor consultado.
    */
   private static function resolve_author_id(
      mixed $queriedObject
   ): int {
      if ($queriedObject instanceof \WP_User) {
         return absint(
            $queriedObject->ID
         );
      }

      return 0;
   }

   /**
    * Determina el número actual de paginación.
    */
   private static function resolve_paged(): int
   {
      $paged = absint(get_query_var('paged'));

      if ($paged < 1) {
         $paged = absint(get_query_var('page'));
      }

      return max(1, $paged);
   }

   /**
    * Lee el módulo solicitado explícitamente.
    */
   private static function resolve_requested_module(): string
   {
      if (!isset($_GET['modulo'])) {
         return '';
      }

      return sanitize_key(
         wp_unslash($_GET['modulo'])
      );
   }
}