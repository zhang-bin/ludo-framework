<?php

namespace Ludo\Support\Facades;

use Ludo\Context\Repository;


/**
 * @see Repository
 *
 * @method static mixed get(string $id, $default = null, $coroutineId = null)
 * @method static void set(string $id, $value)
 */
class Context extends Facade implements FacadeInterface
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