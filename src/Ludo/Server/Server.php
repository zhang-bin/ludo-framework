<?php

namespace Ludo\Server;

use Swoole\Server as SwooleServer;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use RuntimeException;

class Server implements ServerInterface
{

    /**
     * @var string process name
     */
    private $processName;

    /**
     * @var SwooleServer $server
     */
    private $server;

    public function __construct(string $processName)
    {
        $this->processName = $processName;

        if (PHP_OS == 'Linux') {
            swoole_set_process_name(sprintf('php %s manager', $processName));
        }
    }

    public function init(array $config): void
    {
        $mode = $config['mode'] ?? SWOOLE_PROCESS;
        $sockType = $config['sock_type'] ?? SWOOLE_SOCK_TCP;

        $this->server = $this->makeServer($config['type'], $config['host'], $config['port'], $mode, $sockType);
        $this->server->set($config['settings']);

        foreach ($config['callbacks'] as $eventName => $callback) {
            [$className, $method] = $callback;
            $class = new $className();
            /**
             * @var $class ServerCallback
             */
            if (method_exists($class, 'setConfig')) {
                $class->setConfig($this->server, $this->processName, $config);
            }

            $this->server->on($eventName, [$class, $method]);
        }

        if (!empty($config['processes'])) {
            foreach ($config['processes'] as $callback) {
                [$className, $method] = $callback;
                $class = new $className();
                /**
                 * @var $class ServerCallback
                 */
                if (method_exists($class, 'setConfig')) {
                    $class->setConfig($this->processName, $config);
                }
                $this->server->addProcess($class->$method());
            }
        }
    }

    public function start(): void
    {
        $this->server->start();
    }

    protected function makeServer(int $type, string $host, string $port, int $mode, int $sockType)
    {
        switch ($type) {
            case ServerInterface::SERVER_TCP:
                return new SwooleServer($host, $port, $mode, $sockType);
            case ServerInterface::SERVER_HTTP:
                return new SwooleHttpServer($host, $port, $mode, $sockType);
            case ServerInterface::SERVER_WEB_SOCKET:
                return new SwooleWebSocketServer($host, $port, $mode, $sockType);
            default:
                throw new RuntimeException('Server type is invalid');
        }
    }
}