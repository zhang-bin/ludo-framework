<?php

namespace Ludo\Support\Facades;

use RuntimeException;
use Ludo\Support\ServiceProvider;

abstract class Facade implements FacadeInterface
{
    public static function __callStatic($method, $args)
    {
        $instance = ServiceProvider::getMainInstance()->getRegisteredAbstract(static::getFacadeAccessor());
        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        return $instance->$method(...$args);
    }
}