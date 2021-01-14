<?php

namespace Ludo\Database\Connectors;

use PDO;


/**
 * MySql Connector
 *
 * @package Ludo\Database\Connectors
 */
class MySqlConnector extends Connector implements ConnectorInterface
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

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $options = $this->getOptions($config);

        $connection = $this->createConnection($dsn, $config, $options);

        $collation = $config['collation'];
        $charset = $config['charset'];
        $names = "set names {$charset}" . (is_null($collation) ? '' : " collate {$collation}");
        $connection->prepare($names)->execute();

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

        $dsn = "mysql:host={$config['host']};dbname={$config['database']}";

        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        // Sometimes the developer may specify the specific UNIX socket that should
        // be used. If that is the case we will add that option to the string we
        // have created so that it gets utilized while the connection is made.
        if (isset($config['unix_socket'])) {
            $dsn .= ";unix_socket={$config['unix_socket']}";
        }
        return $dsn;
    }
}
