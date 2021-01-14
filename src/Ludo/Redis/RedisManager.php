<?php

namespace Ludo\Redis;

use InvalidArgumentException;
use Ludo\Support\ServiceProvider;


/**
 * Redis Manager
 *
 * @package Ludo\Redis
 */
class RedisManager
{
    /**
     * The active connection instances.
     *
     * @var BaseRedis[] $connections redis connection
     */
    protected array $connections = [];

    /**
     * @var array $config redis config
     */
    protected array $config = [];

    /**
     * RedisManager constructor.
     *
     * @param array $config redis config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a redis connection instance.
     *
     * @param ?string $name connection name
     * @return BaseRedis
     */
    public function connection(string $name = null): BaseRedis
    {
        $name = $name ?: $this->getDefaultConnection();
        if (!isset($this->connections[$name])) {
            $config = $this->getConfig($name);
            $this->connections[$name] = new BaseRedis($config);
        }
        return $this->connections[$name];
    }

    /**
     * Disconnect from the given redis.
     *
     * @param ?string $name connection name
     * @return void
     */
    public function disconnect(string $name = null): void
    {
        $name = $name ?: $this->getDefaultConnection();
        $this->connections[$name]->close();
        $this->connections[$name] = null;

        $provider = ServiceProvider::getInstance();
        if (is_object($provider)) {
            $provider->delRedisHandler($name);
        }
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->config['default'];
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string $name config name
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function getConfig(string $name): array
    {
        $name = $name ?: $this->getDefaultConnection();
        $connections = $this->config['connections'];
        if (is_null($config = array_get($connections, $name))) {
            throw new InvalidArgumentException(sprintf('Redis [%s] not configured.', $name));
        }

        return $config;
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
}