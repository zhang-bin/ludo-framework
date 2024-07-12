<?php

$facades = [
    Ludo\Config\ConfigServiceProvider::class,
    Ludo\Encryption\EncryptionServiceProvider::class,
    Ludo\Context\ContextServiceProvider::class,
    Ludo\Translation\TranslationServiceProvider::class,
];

foreach ($facades as $facade) {
    if (is_string($facade)) {
        $facade = new $facade();
    }

    if (method_exists($facade, 'register')) {
        $facade->register();
    }
}

