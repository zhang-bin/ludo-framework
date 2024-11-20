<?php

namespace Ludo\Support\Facades;

use Ludo\Config\Repository;


/**
 * @see Repository
 *
 * @method static mixed get(string $name)
 * @method static void set(string $name, $value)
 */
class Config extends Facade implements FacadeInterface
{
    /**
     * Get facade accessor
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return Repository::class;
    }
}