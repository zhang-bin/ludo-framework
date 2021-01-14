<?php

namespace Ludo\Database\Builders;

use Ludo\Database\Connection;
use InvalidArgumentException;


/**
 * Builder Factory
 *
 * @package Ludo\Database\Builders
 */
class BuilderFactory
{
    /**
     * get a aql builder based on the configuration.
     *
     * @param Connection $connection database connection object
     * @param string $tableName table name
     * @param string $tableAlias table alias
     *
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function make(Connection $connection, string $tableName, string $tableAlias = ''): Builder
    {
        $driver = $connection->getDriverName();
        switch ($driver) {
            case 'mysql':
                return new MySqlBuilder($connection, $tableName, $tableAlias);
            case 'pgsql':
                return new PgSqlBuilder($connection, $tableName, $tableAlias);
        }

        throw new InvalidArgumentException(sprintf('Unsupported driver [%s]', $driver));
    }
}
