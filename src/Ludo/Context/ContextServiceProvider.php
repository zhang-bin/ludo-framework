<?php
namespace Ludo\Context;

use Ludo\Support\ServiceProvider;
use Ludo\Support\ServiceProviderInterface;

class ContextServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        ServiceProvider::getInstance()->register(Repository::class, function() {
            return new Repository();
        });
    }
}