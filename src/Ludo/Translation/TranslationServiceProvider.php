<?php

namespace Ludo\Translation;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;

class TranslationServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        ServiceProvider::getInstance()->register(Translator::class, function () {
            return new Translator();
        });
    }
}