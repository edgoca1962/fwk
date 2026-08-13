<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Context;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Contexto visual de WP FRW.
 *
 * Contiene todos los atributos que determinan:
 *
 * - clases CSS;
 * - templates;
 * - títulos;
 * - subtítulos;
 * - visibilidad de regiones;
 * - parámetros visuales;
 * - valores dinámicos usados por index.php.
 *
 * Los atributos se aplican por capas:
 *
 * 1. Valores predeterminados de Core.
 * 2. Valores predeterminados del módulo.
 * 3. Valores específicos del post type.
 * 4. Valores específicos de la solicitud.
 * 5. Filtros externos.
 *
 * @package FWK
 */
final class ViewContext
{
   /**
    * Atributos actuales de la vista.
    *
    * @var array<string, mixed>
    */
   private array $attributes = [];

   /**
    * Historial opcional de las capas aplicadas.
    *
    * Útil para depuración.
    *
    * @var array<int, array{
    *     source: string,
    *     values: array<string, mixed>
    * }>
    */
   private array $history = [];

   /**
    * Inicializa el contexto con valores predeterminados.
    *
    * @param array<string, mixed> $defaults
    */
   public function __construct(array $defaults = [])
   {
      if ($defaults !== []) {
         $this->merge($defaults, 'defaults');
      }
   }

   /**
    * Asigna un atributo.
    */
   public function set(
      string $key,
      mixed $value,
      string $source = 'runtime'
   ): self {
      $key = $this->normalize_key($key);

      if ($key === '') {
         return $this;
      }

      $this->attributes[$key] = $value;

      $this->history[] = [
         'source' => $source,
         'values' => [$key => $value],
      ];

      return $this;
   }

   /**
    * Asigna varios atributos.
    *
    * Los nuevos valores sobrescriben los anteriores.
    *
    * @param array<string, mixed> $attributes
    */
   public function merge(
      array $attributes,
      string $source = 'runtime'
   ): self {
      $normalized = [];

      foreach ($attributes as $key => $value) {
         if (!is_string($key)) {
            continue;
         }

         $key = $this->normalize_key($key);

         if ($key === '') {
            continue;
         }

         $normalized[$key] = $value;
      }

      if ($normalized === []) {
         return $this;
      }

      $this->attributes = array_replace(
         $this->attributes,
         $normalized
      );

      $this->history[] = [
         'source' => $source,
         'values' => $normalized,
      ];

      return $this;
   }

   /**
    * Obtiene un atributo.
    */
   public function get(
      string $key,
      mixed $default = null
   ): mixed {
      $key = $this->normalize_key($key);

      return $this->attributes[$key] ?? $default;
   }

   /**
    * Indica si existe un atributo.
    */
   public function has(string $key): bool
   {
      $key = $this->normalize_key($key);

      return array_key_exists(
         $key,
         $this->attributes
      );
   }

   /**
    * Elimina un atributo.
    */
   public function remove(
      string $key,
      string $source = 'runtime'
   ): self {
      $key = $this->normalize_key($key);

      if (!array_key_exists($key, $this->attributes)) {
         return $this;
      }

      unset($this->attributes[$key]);

      $this->history[] = [
         'source' => $source,
         'values' => [$key => null],
      ];

      return $this;
   }

   /**
    * Devuelve todos los atributos.
    *
    * @return array<string, mixed>
    */
   public function all(): array
   {
      return $this->attributes;
   }

   /**
    * Sustituye completamente el contenido del contexto.
    *
    * @param array<string, mixed> $attributes
    */
   public function replace(
      array $attributes,
      string $source = 'replace'
   ): self {
      $this->attributes = [];
      $this->history = [];

      return $this->merge(
         $attributes,
         $source
      );
   }

   /**
    * Devuelve un atributo como booleano.
    */
   public function bool(
      string $key,
      bool $default = false
   ): bool {
      return (bool) $this->get(
         $key,
         $default
      );
   }

   /**
    * Devuelve un atributo como texto.
    */
   public function string(
      string $key,
      string $default = ''
   ): string {
      $value = $this->get(
         $key,
         $default
      );

      if (
         is_string($value)
         || is_numeric($value)
      ) {
         return (string) $value;
      }

      return $default;
   }

   /**
    * Devuelve un atributo que representa una clase CSS.
    */
   public function css(
      string $key,
      string $default = ''
   ): string {
      return $this->string(
         $key,
         $default
      );
   }

   /**
    * Devuelve una ruta de template.
    */
   public function template(
      string $key,
      string $default = ''
   ): string {
      return $this->string(
         $key,
         $default
      );
   }

   /**
    * Carga un template part definido en un atributo.
    *
    * Si el atributo está vacío, no se carga nada.
    *
    * @param array<string, mixed> $args
    */
   public function render(
      string $key,
      array $args = []
   ): void {
      $template = $this->template($key);

      if ($template === '') {
         return;
      }

      get_template_part(
         $template,
         null,
         $args
      );
   }

   /**
    * Devuelve el historial de cambios.
    *
    * @return array<int, array{
    *     source: string,
    *     values: array<string, mixed>
    * }>
    */
   public function history(): array
   {
      return $this->history;
   }

   /**
    * Aplica los filtros finales al contexto sin borrar el historial.
    *
    * Solamente registra en el historial los valores realmente
    * modificados, agregados o eliminados por el filtro.
    */
   public function filter(): self
   {
      $filtered = apply_filters(
         'fwk_view_context',
         $this->attributes,
         $this
      );

      /*
       * Si un filtro devuelve un valor que no es un arreglo,
       * se conserva el contexto actual.
       */
      if (!is_array($filtered)) {
         return $this;
      }

      $changes = [];

      /*
       * Detecta atributos nuevos o modificados.
       */
      foreach ($filtered as $key => $value) {
         if (!is_string($key)) {
            continue;
         }

         $key = $this->normalize_key($key);

         if ($key === '') {
            continue;
         }

         if (
            !array_key_exists($key, $this->attributes)
            || $this->attributes[$key] !== $value
         ) {
            $changes[$key] = $value;
         }
      }

      /*
       * Detecta atributos eliminados por algún filtro.
       */
      foreach ($this->attributes as $key => $value) {
         if (!array_key_exists($key, $filtered)) {
            $changes[$key] = null;
         }
      }

      /*
       * Conserva como estado final el arreglo devuelto
       * por los filtros.
       */
      $this->attributes = $filtered;

      /*
       * Solo agrega una entrada al historial si el filtro
       * produjo cambios reales.
       */
      if ($changes !== []) {
         $this->history[] = [
            'source' => 'filter:fwk_view_context',
            'values' => $changes,
         ];
      }

      return $this;
   }
   /**
    * Normaliza la clave interna.
    */
   private function normalize_key(
      string $key
   ): string {
      return trim($key);
   }
}