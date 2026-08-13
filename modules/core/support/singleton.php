<?php

declare(strict_types=1);

namespace FWK\Modules\Core\Support;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Proporciona una instancia única por clase durante la solicitud.
 *
 * Debe utilizarse únicamente en servicios y módulos que representen
 * una instancia global dentro del ciclo de vida de WordPress.
 *
 * @package FWK
 */
trait Singleton
{
   /**
    * Devuelve la instancia única de la clase invocada.
    *
    * @return static
    */
   final public static function get_instance(): static
   {
      /** @var array<class-string, object> $instances */
      static $instances = [];

      $calledClass = static::class;

      if (!isset($instances[$calledClass])) {
         $instances[$calledClass] = new static();

         /**
          * Se conserva un hook genérico y otro específico.
          *
          * El hook genérico facilita escuchar la creación de cualquier
          * Singleton. El específico permite extender una clase concreta.
          */
         do_action('fwk_singleton_initialized', $instances[$calledClass]);

         do_action(
            'fwk_singleton_initialized_' . sanitize_key($calledClass),
            $instances[$calledClass]
         );
      }

      /** @var static */
      return $instances[$calledClass];
   }

   /**
    * Impide clonar la instancia.
    */
   private function __clone(): void
   {
   }

   /**
    * Impide reconstruir el objeto mediante unserialize().
    *
    * @throws \LogicException
    */
   final public function __wakeup(): void
   {
      throw new \LogicException(
         sprintf(
            'No se puede reconstruir la instancia Singleton de %s.',
            static::class
         )
      );
   }
}