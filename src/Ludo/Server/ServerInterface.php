<?php

namespace Ludo\Server;

/**
 * Server Interface
 *
 * @package Ludo\Server
 */
interface ServerInterface
{
    /**
     * Init server
     *
     * @param array $config server config
     */
    public function init(array $config): void;

    /**
     * Start server
     */
    public function start(): void;
}