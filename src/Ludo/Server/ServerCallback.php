<?php

namespace Ludo\Server;

use Swoole\Server;

class ServerCallback
{
    private $config;
    private $processName;

    public function setConfig($processName, $config)
    {
        $this->config = $config;
        $this->processName = $processName;
    }

    public function start(Server $server)
    {
        if (PHP_OS == 'Linux') {
            swoole_set_process_name(sprintf('php %s master', $this->processName));
        }
    }

    public function workerStart(Server $server, int $workerId)
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