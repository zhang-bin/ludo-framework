<?php

namespace Ludo\Context;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;


/**
 * Service provider for context
 *
 * @package Ludo\Context
 */
class ContextServiceProvider implements ServiceProviderInterface
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