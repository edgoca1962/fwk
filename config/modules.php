<?php

declare(strict_types=1);

use FWK\Modules\Post\Post;
use FWK\Modules\SGF\SGF;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Módulos activos de WP FRW.
 *
 * El orden puede ser relevante cuando un módulo depende de otro.
 */
return [
   Post::class,
   // SGF::class,
];