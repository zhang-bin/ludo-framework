<?php

namespace Ludo\Server;

use Swoole\Server as SwooleServer;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use RuntimeException;


/**
 * Class Server
 *
 * @package Ludo\Server
 */
class Server implements ServerInterface
{

    /**
     * tcp server
     */
    const SERVER_TCP = 1;

    /**
     * http server
     */
    const SERVER_HTTP = 2;

    /**
     * web socket server
     */
    const SERVER_WEB_SOCKET = 3;

    /**
     * @var string $processName process name
     */
    private string $processName;

    /**
     * @var SwooleServer $server server object
     */
    private SwooleServer $server;

    /**
     * Server constructor.
     *
     * @param string $processName process name
     */
    public function __construct(string $processName)
    {
        $this->processName = $processName;

        if (PHP_OS == 'Linux') {
            swoole_set_process_name(sprintf('php %s manager', $processName));
        }
    }

    /**
     * Init server
     *
     * @param array $config server config
     */
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
                    $class->setConfig($this->server, $this->processName, $config);
                }
                $this->server->addProcess($class->$method());
            }
        }
    }

    /**
     * Start server
     */
    public function start(): void
    {
        $this->server->start();
    }

    /**
     * Get server object
     *
     * @param int $type server type
     * @param string $host server host
     * @param string $port server port
     * @param int $mode socket mode
     * @param int $sockType socket type
     * @return SwooleHttpServer|SwooleServer|SwooleWebSocketServer
     */
    protected function makeServer(int $type, string $host, string $port, int $mode, int $sockType)
    {
        switch ($type) {
            case self::SERVER_TCP:
                return new SwooleServer($host, $port, $mode, $sockType);
            case self::SERVER_HTTP:
                return new SwooleHttpServer($host, $port, $mode, $sockType);
            case self::SERVER_WEB_SOCKET:
                return new SwooleWebSocketServer($host, $port, $mode, $sockType);
            default:
                throw new RuntimeException('Server type is invalid');
        }
    }
}