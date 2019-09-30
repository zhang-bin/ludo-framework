<?php

namespace Ludo\Redis;

use InvalidArgumentException;
use Ludo\Support\ServiceProvider;

class RedisManager
{
    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Config
     *
     * @var array
     */
    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a redis connection instance.
     *
     * @param string $name
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
     * @param string $name
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
     * @param string $name
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
}