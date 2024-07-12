<?php

namespace Ludo\Support\Facades;

use Ludo\Encryption\Encryptor;


/**
 * @see Encrypter
 *
 * @method static encrypt($value)
 * @method static decrypt($payload)
 */
class Crypt extends Facade implements FacadeInterface
{
    /**
     * Get facade accessor
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return Encryptor::class;
    }
}