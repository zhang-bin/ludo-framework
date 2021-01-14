<?php

namespace Ludo\Log;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;


/**
 * Service provider for log
 *
 * @package Ludo\Log
 */
class LogServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Service Provider
     */
    public function register(): void
    {
        ServiceProvider::getMainInstance()->register(Logger::class, function () {
            return new Logger();
        });
    }
}