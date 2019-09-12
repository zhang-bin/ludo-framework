<?php

namespace Ludo\Support\Facades;

use Ludo\Context\Repository;


/**
 * @see Repository
 *
 * @method static get(string $id, $default = null, $coroutineId = null)
 * @method static set(string $id, $value)
 */
class Context extends Facade implements FacadeInterface
{
    public static function getFacadeAccessor(): string
    {
        return Repository::class;
    }
}