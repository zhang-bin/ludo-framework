<?php
namespace Ludo\Database\Builders;

use Ludo\Database\Connection;

class BuilderFactory
{
    /**
     * get a aql builder based on the configuration.
     *
     * @param Connection   $connection
     * @param string $tableName
     * @param string $tableAlias
     *
     * @return \Ludo\Database\Builders\Builder
     */
    public function make(Connection $connection, $tableName, $tableAlias = '')
    {
        $driver = $connection->getDriverName();
        switch ($driver) {
            case 'mysql':
                return new MySqlBuilder($connection, $tableName, $tableAlias);
            case 'pgsql':
                return new PgSqlBuilder($connection, $tableName, $tableAlias);
        }

        throw new \InvalidArgumentException("Unsupported driver [$driver]");
    }
}
