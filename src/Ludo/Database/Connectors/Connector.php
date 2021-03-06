<?php

namespace Ludo\Database\Connectors;

use PDO;


/**
 * Database Connector
 *
 * @package Ludo\Database\Connectors
 */
class Connector
{

    /**
     * The default PDO connection options.
     *
     * @var array $options
     */
    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * Get the PDO options based on the configuration.
     *
     * @param array $config connection config
     * @return array
     */
    public function getOptions(array $config): array
    {
        $options = array_get($config, 'options', []);
        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * Create a new PDO connection.
     *
     * @param string $dsn pdo dsn
     * @param array $config connection config
     * @param array $options connection options
     * @return PDO
     */
    public function createConnection(string $dsn, array $config, array $options): PDO
    {
        $username = array_get($config, 'username');
        $password = array_get($config, 'password');
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     *
     * @param array $options connection options
     * @return void
     */
    public function setDefaultOptions(array $options): void
    {
        $this->options = $options;
    }
}

