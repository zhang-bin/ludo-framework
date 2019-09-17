<?php

namespace Ludo\Server;

interface ServerInterface
{
    const SERVER_TCP = 1;
    const SERVER_HTTP = 2;
    const SERVER_WEB_SOCKET = 3;

    public function __construct(string $processName);

    public function init(array $config): void;

    public function start(): void;
}