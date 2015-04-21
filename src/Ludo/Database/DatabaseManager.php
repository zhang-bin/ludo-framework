<?php
namespace Ludo\Database;

use Ludo\Database\Connectors\ConnectionFactory;

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
     * @var \Ludo\Database\Connectors\ConnectionFactory
     */
    protected $factory;

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = array();

    /**
     * Create a new database manager instance.
     *
     * @param  array  $config
     * @param  \Ludo\Database\Connectors\ConnectionFactory  $factory
     */
    public function __construct($config, ConnectionFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Ludo\Database\Connection
     */
    public function connection($name = null)
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
     * @param  string  $name
     * @return \Ludo\Database\Connection
     */
    public function reconnect($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        $this->disconnect($name);

        return $this->connection($name);
    }

    /**
     * Disconnect from the given database.
     *
     * @param  string  $name
     * @return void
     */
    public function disconnect($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();
        unset($this->connections[$name]);
    }

    /**
     * Make the database connection instance.
     *
     * @param  string  $name
     * @return \Ludo\Database\Connection
     */
    protected function makeConnection($name)
    {
        $config = $this->getConfig($name);
        return $this->factory->make($config, $name);
    }

    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getConfig($name)
    {
        $name = $name ?: $this->getDefaultConnection();
        $connections = $this->config['database.connections'];
        if (is_null($config = array_get($connections, $name))) {
            throw new \InvalidArgumentException("Database [$name] not configured.");
        }

        return $config;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->config['database.default'];
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->config['database.default'] = $name;
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }
}
