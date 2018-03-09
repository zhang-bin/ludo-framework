<?php
namespace Ludo\Encrypter;

use Ludo\Support\ServiceProvider;
use Ludo\Config\Config;

class EncrypterServiceProvider {
    public function register() {
        ServiceProvider::getInstance()->register('encrypter', function(){
            return new Encrypter();
        });
    }
}