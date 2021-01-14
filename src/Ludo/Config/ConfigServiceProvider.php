<?php

namespace Ludo\Config;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;


/**
 * Service provider for config
 *
 * @package Ludo\Config
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Service Provider
     */
    public function register(): void
    {
        ServiceProvider::getMainInstance()->register(Repository::class, function () {
            return new Repository();
        });
    }
}