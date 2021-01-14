<?php

namespace Ludo\Support;


/**
 * Service Provider Interface
 *
 * @package Ludo\Support
 */
interface ServiceProviderInterface
{
    /**
     * Register Service Provider
     */
    public function register(): void;
}