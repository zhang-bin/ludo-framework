<?php

namespace Ludo\Database;

use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Support\ServiceProvider;
use InvalidArgumentException;


/**
 * Database Manager
 *
 * @package Ludo\Database
 */
class DatabaseManager
{
    /**
     * @var array $app config instance
     */
    protected array $app;

    /**
     * @var ConnectionFactory $factory database connection factory instance
     */
    protected ConnectionFactory $factory;

    /**
     * @var array $connections active connection instances
     */
    protected array $connections = [];

    /**
     * @var array $config database config
     */
    protected array $config = [];

    /**
     * Create a new database manager instance.
     *
     * @param array $config database config
     * @param ConnectionFactory $factory connection factory
     */
    public function __construct(array $config, ConnectionFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Get a database connection instance.
     *
     * @param ?string $name connection name
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
     * @param ?string $name connection name
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
     * @param ?string $name connection name
     * @return void
     */
    public function disconnect(string $name = null): void
    {
        $name = $name ?: $this->getDefaultConnection();
        $this->connections[$name] = null;

        ServiceProvider::getInstance()->delDBHandler($name);
    }

    /**
     * Make the database connection instance.
     *
     * @param string $name connection name
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
     * @param string $name connection name
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function getConfig(string $name): array
    {
        $name = $name ?: $this->getDefaultConnection();
        $connections = $this->config['connections'];
        if (is_null($config = array_get($connections, $name))) {
            throw new InvalidArgumentException(sprintf('Database [%s] not configured.', $name));
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
     * @param string $name connection name
     * @return void
     */
    public function setDefaultConnection(string $name): void
    {
        $this->config['default'] = $name;
    }

    /**
     * Return all the created connections.
     *
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
}
