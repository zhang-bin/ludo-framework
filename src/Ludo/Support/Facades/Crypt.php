<?php
namespace Ludo\Support\Facades;

use Ludo\Encryption\Encrypter;


/**
 * @see Encrypter
 *
 * @method static encrypt($value)
 * @method static decrypt($payload)
 */
class Crypt extends Facade implements FacadeInterface
{
    public static function getFacadeAccessor(): string
    {
        return Encrypter::class;
    }
}