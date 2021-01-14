<?php

namespace Ludo\Database\Connectors;

use PDO;


/**
 * Connector Interface
 * @package Ludo\Database\Connectors
 */
interface ConnectorInterface
{

    /**
     * Establish a database connection.
     *
     * @param array $config connection config
     * @return PDO
     */
    public function connect(array $config): PDO;
}
