<?php

namespace Ludo\Support\Facades;

use RuntimeException;
use Ludo\Support\ServiceProvider;


abstract class Facade implements FacadeInterface
{
    /**
     * Call facade static method
     *
     * @param string $method method name
     * @param array $args method arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = ServiceProvider::getMainInstance()->getRegisteredAbstract(static::getFacadeAccessor());
        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        return $instance->$method(...$args);
    }

    /**
     * Call facade method
     *
     * @param string $method method name
     * @param array $args method arguments
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        $instance = ServiceProvider::getMainInstance()->getRegisteredAbstract(static::getFacadeAccessor());
        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        return $instance->$method(...$args);
    }
}