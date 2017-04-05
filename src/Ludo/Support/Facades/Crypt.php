<?php
namespace Ludo\Support\Facades;

/**
 * @see \Ludo\Encrypter\Encrypter
 * @method static
 */
class Crypt extends Facade {
    public static function getFacadeAccessor()
    {
        return 'encrypter';
    }
}