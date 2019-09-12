<?php
namespace Ludo\Encryption;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;

class EncryptionServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        ServiceProvider::getInstance()->register(Encrypter::class, function() {
            return new Encrypter();
        });
    }
}