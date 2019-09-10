<?php
\Ludo\Foundation\Lang::init();

$providers = [
    Ludo\Encrypter\EncrypterServiceProvider::class,
];

foreach ($providers as $provider) {
    if (is_string($provider)) $provider = new $provider();
    if (method_exists($provider, 'register')) {
        $provider->register();
    }
}

$alias = [
    'Filter' => Ludo\Support\Filter::class,
    'Validator' => Ludo\Support\Validator::class,
    'Lang' => Ludo\Foundation\Lang::class,
    'Config' => Ludo\Config\Config::class,
    'Factory' => Ludo\Support\Factory::class,
    'View' => Ludo\View\View::class,
    'QueryException' => Ludo\Database\QueryException::class,
    'Counter' => Ludo\Counter\Counter::class,
    'Crypt' => Ludo\Support\Facades\Crypt::class,
];
\Ludo\Support\AliasLoader::getInstance($alias)->register();

