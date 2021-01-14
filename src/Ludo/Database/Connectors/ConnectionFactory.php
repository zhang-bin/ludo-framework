<?php

namespace Ludo\Database\Connectors;

use PDO;
use Ludo\Database\MySqlConnection;
use Ludo\Database\PgSqlConnection;
use Ludo\Database\Connection;
use InvalidArgumentException;


/**
 * Connection Factory
 *
 * @package Ludo\Database\Connectors
 */
class ConnectionFactory
{
    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param array $config connection config
     * @param ?string $name connection name
     * @return Connection
     */
    public function make(array $config, string $name = null): Connection
    {
        $config = $this->parseConfig($config, $name);
        if (isset($config['read'])) {
            return $this->createReadWriteConnection($config);
        } else {
            return $this->createSingleConnection($config);
        }
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config connection config
     * @return Connection
     */
    protected function createSingleConnection(array $config): Connection
    {
        $pdo = $this->createConnector($config)->connect($config);
        return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config connection config
     * @return Connection
     */
    protected function createReadWriteConnection(array $config): Connection
    {
        $connection = $this->createSingleConnection($this->getWriteConfig($config));
        return $connection->setReadPdo($this->createReadPdo($config));
    }

    /**
     * Create a new PDO instance for reading.
     *
     * @param array $config connection config
     * @return PDO
     */
    protected function createReadPdo(array $config): PDO
    {
        $readConfig = $this->getReadConfig($config);
        return $this->createConnector($readConfig)->connect($readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param array $config connection config
     * @return array
     */
    protected function getReadConfig(array $config): array
    {
        $readConfig = $this->getReadWriteConfig($config, 'read');
        return $this->mergeReadWriteConfig($config, $readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param array $config connection config
     * @return array
     */
    protected function getWriteConfig(array $config): array
    {
        $writeConfig = $this->getReadWriteConfig($config, 'write');
        return $this->mergeReadWriteConfig($config, $writeConfig);
    }

    /**
     * Get a read / write level configuration.
     *
     * @param array $config connection config
     * @param string $type connection type
     * @return array
     */
    protected function getReadWriteConfig(array $config, string $type): array
    {
        if (isset($config[$type][0])) {
            return $config[$type][array_rand($config[$type])];
        } else {
            return $config[$type];
        }
    }

    /**
     * Merge a configuration for a read / write connection.
     *
     * @param array $config connection config
     * @param array $merge other connection config
     * @return array
     */
    protected function mergeReadWriteConfig(array $config, array $merge): array
    {
        return array_except(array_merge($config, $merge), ['read', 'write']);
    }

    /**
     * Parse and prepare the database configuration.
     *
     * @param array $config connection config
     * @param string $name connection name
     * @return array
     */
    protected function parseConfig(array $config, string $name): array
    {
        return array_add(array_add($config, 'prefix', ''), 'name', $name);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config connection config
     * @return ConnectorInterface
     *
     * @throws InvalidArgumentException
     */
    public function createConnector(array $config): ConnectorInterface
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        switch ($config['driver']) {
            case 'mysql':
                return new MySqlConnector;
            case 'pgsql':
                return new PgSqlConnector;
        }

        throw new InvalidArgumentException(sprintf('Unsupported driver [%s]', $config['driver']));
    }

    /**
     * Create a new connection instance.
     *
     * @param string $driver database
     * @param PDO $connection database connection
     * @param string $database database name
     * @param string $prefix prefix of table name
     * @param array $config connection config
     * @return Connection
     *
     * @throws InvalidArgumentException
     */
    protected function createConnection(string $driver, PDO $connection, string $database, string $prefix = '', array $config = []): Connection
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
            case 'pgsql':
                return new PgSqlConnection($connection, $database, $prefix, $config);
        }

        throw new InvalidArgumentException(sprintf('Unsupported driver [%s]', $driver));
    }
}

