<?php

namespace Ludo\Encryption;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;


/**
 * Service provider for encryption
 *
 * @package Ludo\Encryption
 */
class EncryptionServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Service Provider
     */
    public function register(): void
    {
        ServiceProvider::getMainInstance()->register(Encrypter::class, function () {
            return new Encrypter();
        });
    }
}