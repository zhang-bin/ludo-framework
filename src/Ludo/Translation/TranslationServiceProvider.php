<?php

namespace Ludo\Translation;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;


/**
 * Service Provider for Translation
 *
 * @package Ludo\Translation
 */
class TranslationServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Service Provider
     */
    public function register(): void
    {
        ServiceProvider::getMainInstance()->register(Translator::class, function () {
            return new Translator();
        });
    }
}