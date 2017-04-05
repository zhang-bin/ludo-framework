<?php
namespace Ludo\Encrypter;

use Ludo\Support\ServiceProvider;
use Ludo\Config\Config;

class EncrypterServiceProvider {
    public function register() {
        ServiceProvider::getInstance()->register('encrypter', function(){
            $config = Config::get('app');
            return new Encrypter($config['key'], $config['cipher']);
        });
    }
}