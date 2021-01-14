<?php

namespace Ludo\Server;

use Swoole\Server;


/**
 * Class ServerCallback
 *
 * @package Ludo\Server
 */
class ServerCallback
{
    /**
     * @var array $config server config
     */
    protected array $config;

    /**
     * @var string $processName process name
     */
    private string $processName;

    /**
     * @var Server $server server object
     */
    protected Server $server;

    /**
     * Set server config
     *
     * @param Server $server server object
     * @param string $processName process name
     * @param array $config server config
     */
    public function setConfig(Server $server, string $processName, array $config): void
    {
        $this->config = $config;
        $this->processName = $processName;
        $this->server = $server;
    }

    /**
     * Start main process
     *
     * @param Server $server server object
     */
    public function start(Server $server): void
    {
        if (PHP_OS == 'Linux') {
            swoole_set_process_name(sprintf('php %s master', $this->processName));
        }
    }

    /**
     * Start worker process
     *
     * @param Server $server server object
     * @param int $workerId worker id
     */
    public function workerStart(Server $server, int $workerId): void
    {
        if (PHP_OS == 'Linux') {
            if ($workerId >= $this->config['worker_num']) {
                swoole_set_process_name(sprintf('php %s task worker', $this->processName));
            } else {
                swoole_set_process_name(sprintf('php %s event worker', $this->processName));
            }
        }
    }
}