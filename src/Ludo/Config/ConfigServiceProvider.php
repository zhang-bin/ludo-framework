<?php

namespace Ludo\Config;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        ServiceProvider::getMainInstance()->register(Repository::class, function () {
            return new Repository();
        });
    }
}