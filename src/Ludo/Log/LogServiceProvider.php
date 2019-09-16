<?php

namespace Ludo\Log;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;

class LogServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        ServiceProvider::getMainInstance()->register(Logger::class, function () {
            return new Logger();
        });
    }
}