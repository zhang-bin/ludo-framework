<?php

namespace Ludo\Database\Connectors;

use PDO;


/**
 * PostgreSql Connector
 *
 * @package Ludo\Database\Connectors
 */
class PgSqlConnector extends Connector implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param array $config connection config
     * @return PDO
     */
    public function connect(array $config): PDO
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        $connection = $this->createConnection($dsn, $config, $options);

        $charset = $config['charset'];

        $connection->prepare("set names '$charset'")->execute();

        // Unlike MySQL, Postgres allows the concept of "schema" and a default schema
        // may have been specified on the connections. If that is the case we will
        // set the default schema search paths to the specified database schema.
        if (isset($config['schema'])) {
            $schema = $config['schema'];
            $connection->prepare("set search_path to {$schema}")->execute();
        }
        return $connection;
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param array $config connection config
     * @return string
     */
    protected function getDsn(array $config): string
    {
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.

        $host = isset($config['host']) ? "host={$config['host']};" : '';
        $dsn = "pgsql:{$host}dbname={$config['database']}";

        // If a port was specified, we will add it to this Postgres DSN connections
        // format. Once we have done that we are ready to return this connection
        // string back out for usage, as this has been fully constructed here.
        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }
        return $dsn;
    }
}
