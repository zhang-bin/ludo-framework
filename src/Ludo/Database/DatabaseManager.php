<?php

namespace Ludo\Database;

use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Support\ServiceProvider;
use InvalidArgumentException;

class DatabaseManager
{
    /**
     * The config instance.
     *
     * @var array
     */
    protected $app;

    /**
     * The database connection factory instance.
     *
     * @var ConnectionFactory
     */
    protected $factory;

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

    /**
     * Create a new database manager instance.
     *
     * @param array $config
     * @param ConnectionFactory $factory
     */
    public function __construct(array $config, ConnectionFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Get a database connection instance.
     *
     * @param string $name
     * @return Connection
     */
    public function connection(string $name = null): Connection
    {
        $name = $name ?: $this->getDefaultConnection();
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }
        return $this->connections[$name];
    }

    /**
     * Reconnect to the given database.
     *
     * @param string $name
     * @return Connection
     */
    public function reconnect(string $name = null): Connection
    {
        $name = $name ?: $this->getDefaultConnection();

        $this->disconnect($name);

        return $this->connection($name);
    }

    /**
     * Disconnect from the given database.
     *
     * @param string $name
     * @return void
     */
    public function disconnect(string $name = null): void
    {
        $name = $name ?: $this->getDefaultConnection();
        $this->connections[$name] = null;

        $provider = ServiceProvider::getInstance();
        if (is_object($provider)) {
            $provider->delDBHandler($name);
        }
    }

    /**
     * Make the database connection instance.
     *
     * @param string $name
     * @return Connection
     */
    protected function makeConnection(string $name): Connection
    {
        $config = $this->getConfig($name);
        return $this->factory->make($config, $name);
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
            throw new InvalidArgumentException("Database [$name] not configured.");
        }

        return $config;
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
     * Set the default connection name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultConnection(string $name): void
    {
        $this->config['default'] = $name;
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
