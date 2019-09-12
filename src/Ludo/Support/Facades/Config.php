<?php
namespace Ludo\Support\Facades;

use Ludo\Config\Repository;


/**
 * @see Repository
 *
 * @method static get(string $name)
 * @method static set(string $name, $value)
 */
class Config extends Facade implements FacadeInterface
{
    public static function getFacadeAccessor(): string
    {
        return Repository::class;
    }
}