<?php

namespace Ludo\Database;

use Ludo\Support\ServiceProvider;
use Ludo\Database\Builders\BuilderFactory;
use Ludo\Database\Builders\Builder;
use PDO;


/**
 * Class Dao
 *
 * @package Ludo\Database
 */
abstract class Dao
{
    /**
     * @var Connection $connection connection object
     */
    protected Connection $connection;

    /**
     * @var Builder $builder builder object
     */
    protected Builder $builder;

    /**
     * @var string $tblName table name
     */
    protected string $tblName;

    /**
     * @param string $tblName table name
     * @param ?string $connectionName connection name
     */
    public function __construct(string $tblName, string $connectionName = null)
    {
        $this->tblName = $tblName;
        $this->connection = ServiceProvider::getInstance()->getDBHandler($connectionName);
        $builderFactory = new BuilderFactory();
        $this->builder = $builderFactory->make($this->connection, $tblName);
    }

    /**
     * Insert data into DB
     *
     * @param array $arr array('field'=>value, 'field2'=>value2);
     * @return int Last insert id if insert successful, else SqlException will be throw
     */
    public function add(array $arr): int
    {
        return $this->builder->insert($arr);
    }

    /**
     * Identical to LdBaseDao::add($arr);
     *
     * @param array $arr array('field'=>value, 'field2'=>value2);
     * @return int Last insert id if insert successful, else SqlException will be throw
     */
    public function insert(array $arr): int
    {
        return $this->add($arr);
    }

    /**
     * Used for batch insert lots data into the table
     *
     * @param array $arr 2D array,
     *    assoc array:            array(array('field'=>value, 'field2'=>value2), array('field'=>value, 'field2'=>value2));
     *    or just indexed array:    array(array(value1, value2), array(value1, value2)); //if use indexedNames, the 2nd argument "$fieldNames" must be passed.
     * @param array|string $fieldNames [Optional] only needed in indexed Data. field names for batch insert
     * @param bool $ignore
     * @return int true if insert successful, else SqlException will be throw
     */
    public function batchInsert(array $arr, array $fieldNames = [], bool $ignore = false): int
    {
        if (empty($arr)) {
            return false;
        }

        $keys = '(';
        if (!empty($fieldNames)) {
            if (is_array($fieldNames)) {
                $comma = '';
                foreach ($fieldNames as $field) {
                    $keys .= $comma . $this->connection->quoteIdentifier($field);
                    $comma = ',';
                }
            } else {
                $keys = $this->connection->quoteIdentifier($fieldNames);
            }
        } else {
            $fields = array_keys($arr[0]);
            $comma = '';
            foreach ($fields as $field) {
                $keys .= $comma . $this->connection->quoteIdentifier($field);
                $comma = ',';
            }
        }
        $keys .= ')';

        $sql = 'INSERT';
        if ($ignore) {
            $sql .= ' IGNORE ';
        }
        $sql .= ' INTO ' . $this->connection->quoteIdentifier($this->tblName) . " {$keys} VALUES ";

        $comma = '';
        $params = [];
        foreach ($arr as $a) {
            $sql .= $comma . '(';
            $comma2 = '';
            foreach ($a as $v) {
                $sql .= $comma2 . '?';
                $params[] = $v;
                $comma2 = ',';
            }
            $sql .= ')';
            $comma = ',';
        }
        return $this->builder->exec($sql, $params);
    }

    /**
     * Update fields of object with id=$id
     *
     * @param int $id primary key of current table
     * @param array $arr update data
     * @return int affected row number
     */
    public function update(int $id, array $arr): int
    {
        return $this->updateWhere($arr, 'id = ?', [$id]);
    }

    /**
     * Update fields of object with some conditions
     *
     * @param array $newData update data
     * @param ?string $condition where condition
     * @param array $params where parameters
     * @return int affected row
     */
    public function updateWhere(array $newData, string $condition = null, array $params = []): int
    {
        return $this->builder->update($newData, $condition, $params);
    }

    /**
     * Delete record with id=$id
     *
     * @param int $id primary key of current table
     * @return int affected row
     */
    public function delete(int $id): int
    {
        return $this->deleteWhere('id = ?', [$id]);
    }

    /**
     * Delete record with condition
     *
     * @param string $condition where condition
     * @param array $params where parameters
     * @return int affected row
     */
    public function deleteWhere(string $condition, array $params = []): int
    {
        return $this->builder->delete($condition, $params);
    }

    /**
     * Get one row from table by ID
     *
     * @param int $id primary key of current table
     * @param string $fields fields needs to be fetched, comma separated
     * @param int $fetchMode pdo fetch mode
     * @return array key is field name and value is field value.
     */
    public function fetch(int $id, string $fields = '', int $fetchMode = PDO::FETCH_ASSOC): array
    {
        if (empty($fields)) {
            $fields = $this->tblName . '.*';
        }

        $this->builder->setField($fields);
        $this->builder->where($this->tblName . '.id = ?', [$id]);
        return $this->builder->fetch($fetchMode);
    }

    /**
     * Get one row from table by condition
     *
     * @param string $condition where condition
     * @param array $params where parameters
     * @param string $fields fields needs to be fetched, comma separated
     * @param int $fetchMode pdo fetch mode
     * @return array
     */
    public function find(string $condition, array $params, string $fields = '', int $fetchMode = PDO::FETCH_ASSOC): array
    {
        if (!empty($fields)) {
            $this->builder->setField($fields);
        }

        return $this->builder->where($condition, $params)->fetch($fetchMode);
    }

    /**
     * Get one column string from table by condition
     *
     * @param string $condition where condition
     * @param array $params where parameters
     * @param string $column column name
     * @return string
     */
    public function findColumn(string $condition, array $params, string $column): string
    {
        if (!empty($column)) {
            $this->builder->setField($column);
        }

        return $this->builder->where($condition, $params)->fetchColumn();
    }

    /**
     * Get one column string from table by id
     *
     * @param int $id primary key of current table
     * @param string $column column name
     * @return string
     */
    public function fetchColumn(int $id, string $column): string
    {
        if (!empty($column)) {
            $this->builder->setField($column);
        }

        return $this->builder->where($this->tblName . '.id = ?', [$id])->fetchColumn();
    }

    /**
     * Get record from table
     *
     * @param int $rows result row number
     * @param int $start result row start
     * @param string $order order statement
     * @param string $fields select fields
     * @param int $fetchMode pdo fetch mode
     * @return array
     */
    public function fetchAll(int $rows = 0, int $start = 0, string $order = '', string $fields = '*', int $fetchMode = PDO::FETCH_ASSOC): array
    {
        return $this->builder->field($fields)->limit($rows, $start)->orderby($order)->fetchAll($fetchMode);
    }

    /**
     * Get one column list from table
     *
     * @param string $fields select fields
     * @param int $rows result row number
     * @param int $start result row start
     * @param string $order order statement
     * @return array
     */
    public function fetchAllUnique(string $fields = '*', int $rows = 0, int $start = 0, string $order = ''): array
    {
        return $this->builder->field($fields)->limit($rows, $start)->orderby($order)->fetchAllUnique();
    }

    /**
     * Get record from table by condition
     *
     * @param string|array $condition where condition
     * @param int $rows result row number
     * @param int $start result row start
     * @param string $order order statement
     * @param string $fields select fields
     * @param int $fetchMode pdo fetch mode
     * @return array
     */
    public function findAll($condition = '', int $rows = 0, int $start = 0, string $order = '', string $fields = '*', int $fetchMode = PDO::FETCH_ASSOC): array
    {
        if (is_array($condition)) {
            $where = $condition[0];
            $params = $condition[1];
        } else {
            $where = $condition;
            $params = [];
        }
        return $this->builder->field($fields)->where($where, $params)->orderby($order)->limit($rows, $start)->fetchAll($fetchMode);
    }

    /**
     * Get one column list from table by condition
     *
     * @param string|array $condition where condition
     * @param string $fields select fields
     * @param int $rows result row number
     * @param int $start result row start
     * @param string $order order statement
     * @return array
     */
    public function findAllUnique($condition = '', string $fields = '', int $rows = 0, int $start = 0, string $order = ''): array
    {
        if (is_array($condition)) {
            $where = $condition[0];
            $params = $condition[1];
        } else {
            $where = $condition;
            $params = [];
        }
        return $this->builder->field($fields)->where($where, $params)->orderby($order)->limit($rows, $start)->fetchAllUnique();
    }

    /**
     * Get key=>value formatted result from table
     *
     * @param string|array $condition where condition
     * @param string $fields select fields
     * @param int $rows result row number
     * @param int $start result row start
     * @param string $order order statement
     * @return array
     */
    public function findAllKvPair($condition = '', string $fields = '', int $rows = 0, int $start = 0, string $order = ''): array
    {
        if (is_array($condition)) {
            $where = $condition[0];
            $params = $condition[1];
        } else {
            $where = $condition;
            $params = [];
        }
        return $this->builder->field($fields)->where($where, $params)->orderby($order)->limit($rows, $start)->fetchAllKvPair();
    }

    /**
     * Count records
     *
     * @param string $condition where condition
     * @param array $params where parameters
     * @param bool $distinct distinct statement
     * @return int
     */
    public function count(string $condition = '', array $params = [], bool $distinct = false): int
    {
        if (!empty($condition)) {
            $this->builder->where($condition, $params);
        }
        return $this->builder->recordsCount($distinct);
    }

    /**
     * Check if the records exists according to the $condition.
     *
     * @param string $condition where condition
     * @param array $params where parameters
     * @return bool
     */
    public function exists(string $condition = '', array $params = []): bool
    {
        if (!is_array($params)) {
            $params = [$params];
        }

        $cnt = $this->builder->setField('count(*)')->where($condition, $params)->fetchColumn();
        return $cnt > 0;
    }

    /**
     * Check if the records exists according to the $condition. if exists, return the row data.
     * result[0] is a bool value represent exists or not.
     * If exists, result[1] will store the "1st db row" result
     *
     * @param string $condition where condition
     * @param array $params where parameters
     * @param ?string $fields select fields
     * @return array list(exists, row) = Array(0=>true/false, 1=>rowArray/false)
     */
    public function existsRow(string $condition = '', array $params = [], string $fields = null): array
    {
        if (!empty($fields)) {
            $this->builder->setField($fields);
        }

        $row = $this->builder->where($condition, $params)->fetch(PDO::FETCH_BOTH);
        $exists = empty($row) ? false : true;
        return [$exists, $row];
    }

    /**
     * Return the max Id from current table
     *
     * @return int the max id
     */
    public function maxId(): int
    {
        return $this->builder->setField('id')->orderby('id DESC')->fetchColumn();
    }

    /**
     * One to one relation.
     *
     * @param string $table table name [and alias] which need to be joined. eg. User as Author
     * @param string $fields the fields you need to retrieve. default is all. E.G. Author.uname as authorUname, Author.nickname as nickname.
     * @param ?string $foreignKey ForeignKey field name. default is null which will use tableName+Id as its FK. eg. userId, productId
     * @param string $joinType one of the three [inner, left, right]. default is left.
     * @return $this
     */
    public function hasA(string $table, string $fields = '', string $foreignKey = null, string $joinType = 'left'): Dao
    {
        //if $table have alias like ('User  author'), extract the table name and alias.
        if (strpos($table, ' ') !== false) {
            $tmp = preg_split('/\s+/', str_replace(' as ', ' ', $table));
            $tblName = ucfirst($tmp[0]);
            $tblAlias = $tmp[1];
        } else {
            $tblName = ucfirst($table);
            $tblAlias = $table;
        }

        $foreignKey = $foreignKey ? $foreignKey : lcfirst($tblName) . 'Id';
        $foreignKey = $this->connection->quoteIdentifier($foreignKey);
        $joinType = $joinType . ' JOIN';

        $tblName = $this->connection->quoteIdentifier($tblName);
        $this->builder->join("$tblName $tblAlias", "$this->tblName.$foreignKey=$tblAlias.id", $fields, $joinType);

        return $this;
    }

    /**
     * Start a new database transaction.
     *
     * @param bool $switchConnection 事务开启后，如果该值为true，那么事务内的查询操作会切换到主库
     */
    public function beginTransaction(bool $switchConnection = true): void
    {
        $this->connection->beginTransaction($switchConnection);
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * Save connection debug log
     *
     * @param ?string $connection connection name
     * @return string
     */
    public function debug(string $connection = null): string
    {
        if (is_null($connection)) {
            $connection = $this->connection;
        }
        return $connection->debug();
    }

    /**
     * Get last executed sql statement
     *
     * @param ?Builder $builder builder object
     * @return string
     */
    public function lastSql(Builder $builder = null): string
    {
        if (is_null($builder)) {
            $builder = $this->builder;
        }
        return $builder->sql();
    }

    /**
     * Return the slave table handler object
     *
     * @return Builder
     */
    public function tbl(): Builder
    {
        return $this->builder;
    }

    /**
     * Get current table name
     *
     * @return string
     */
    public function tblName(): string
    {
        return $this->tblName;
    }

    /**
     * Truncate table
     */
    public function truncate(): void
    {
        $this->connection->statement('TRUNCATE ' . $this->tblName);
    }

    /**
     * Show create table
     *
     * @return array
     */
    public function showCreate(): array
    {
        return $this->connection->select('SHOW CREATE TABLE ' . $this->tblName)[0]['Create Table'];
    }
}
