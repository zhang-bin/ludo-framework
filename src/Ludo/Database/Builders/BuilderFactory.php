<?php

namespace Ludo\Database\Builders;

use Ludo\Database\Connection;
use InvalidArgumentException;

class BuilderFactory
{
    /**
     * get a aql builder based on the configuration.
     *
     * @param Connection $connection
     * @param string $tableName
     * @param string $tableAlias
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
