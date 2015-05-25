<?php
namespace Ludo\Database;

use Ludo\Support\ServiceProvider;
use Ludo\Database\Builders\BuilderFactory;
use PDO;

abstract class Dao
{
    /**
     * Handler of Connection
     *
     * @var \Ludo\Database\Connection
     */
    protected $connection = null;

    /**
     * Instance of LdTable
     *
     * @var \Ludo\Database\Builders\Builder
     */
    protected $builder = null;

    protected $tblName = null;

    /**
     * @param string $tblName
     * @param string $connectionName
     */
    public function __construct($tblName, $connectionName = null)
    {
        $this->tblName = $tblName;
        $this->connection = ServiceProvider::getInstance()->getDBHandler($connectionName);
        $builderFactory = new BuilderFactory();
        $this->builder = $builderFactory->make($this->connection, $tblName);
    }

    /**
     * insert data into DB
     *
     * @param Array $arr array('field'=>value, 'field2'=>value2);
     * @return int Last insert id if insert successful, else SqlException will be throw
     */
    public function add($arr)
    {
        return $this->builder->insert($arr);
    }

    /**
     * identical to LdBaseDao::add($arr);
     *
     * @param Array $arr array('field'=>value, 'field2'=>value2);
     * @return int Last insert id if insert successful, else SqlException will be throw
     */
    public function insert($arr)
    {
        return $this->add($arr);
    }

    /**
     * used for batch insert lots data into the table
     *
     * @param Array $arr 2D array,
     * 	assoc array: 			array(array('field'=>value, 'field2'=>value2), array('field'=>value, 'field2'=>value2));
     * 	or just indexed array:	array(array(value1, value2), array(value1, value2)); //if use indexedNames, the 2nd argument "$fieldNames" must be passed.
     * @param Array|String $fieldNames [Optional] only needed in indexed Data. field names for batch insert
     * @param bool $ignore
     * @return true if insert successful, else SqlException will be throw
     */
    public function batchInsert($arr, $fieldNames = array(), $ignore = false)
    {
        if (empty($arr)) return false;

        $keys = '(';
        if (!empty($fieldNames)) {
            if (is_array($fieldNames)) {
                $comma = '';
                foreach ($fieldNames as $field) {
                    $keys .= $comma.$this->connection->quoteIdentifier($field);
                    $comma = ',';
                }
            } else {
                $keys = $this->connection->quoteIdentifier($fieldNames);
            }
        } else {
            $fields = array_keys($arr[0]);
            $comma = '';
            foreach ($fields as $field) {
                $keys .= $comma.$this->connection->quoteIdentifier($field);
                $comma = ',';
            }
        }
        $keys .= ')';

        $sql = 'INSERT';
        if ($ignore) $sql .= ' IGNORE ';
        $sql .= ' INTO '.$this->connection->quoteIdentifier($this->tblName)." {$keys} VALUES ";

        $comma = '';
        $params = array();
        foreach ($arr as $a) {
            $sql .= $comma.'(';
            $comma2 = '';
            foreach($a as $v) {
                $sql .= $comma2.'?';
                $params[] = $v;
                $comma2 = ',';
            }
            $sql .= ')';
            $comma = ',';
        }
        return $this->builder->exec($sql, $params);
    }

    /**
     * update fields of object with id=$id
     *
     * @param Int $id
     * @param Array $arr
     * @return int affected row number
     */
    public function update($id, $arr)
    {
        return $this->updateWhere($arr, 'id = ?', array($id));
    }

    /**
     * update fields of object with some conditions
     *
     * @param Array $newData
     * @param String $condition
     * @param mixed
     * @return int affected row
     */
    public function updateWhere($newData, $condition, $params = array())
    {
        return $this->builder->update($newData, array($condition, $params));
    }

    /**
     * delete record with id=$id
     *
     * @param $id
     * @return int affected row
     */
    public function delete($id)
    {
        return $this->deleteWhere('id = ?', $id);
    }

    /**
     * delete record with condition
     *
     * @param string $condition
     * @param null|array $params
     * @return int affected row
     */
    public function deleteWhere($condition, $params = null)
    {
        return $this->builder->delete($condition, $params);
    }

    /**
     * get one row from table by ID
     *
     * @param $id
     * @param String $fields fields needs to be fetched, comma separated
     * @param int $fetchMode
     * @return Array key is field name and value is field value.
     */
    public function fetch($id, $fields = '', $fetchMode = PDO::FETCH_ASSOC)
    {
        if (!empty($fields)) $this->builder->setField($fields);
        $this->builder->where($this->tblName.'.id = ?', $id);
        return $this->builder->fetch(null, $fetchMode);
    }

    /**
     * get one row from table by condition
     *
     * @param string $condition
     * @param array $params
     * @param string $fields fields needs to be fetched, comma separated
     * @param int $fetchMode
     * @return array|bool
     */
    public function find($condition, $params, $fields = '', $fetchMode = PDO::FETCH_ASSOC)
    {
        if (!empty($fields)) $this->builder->setField($fields);
        return $this->builder->where($condition, $params)->fetch(NULL, $fetchMode);
    }

    /**
     * get one column string from table by condition
     *
     * @param string $condition
     * @param string|array $params
     * @param string $column
     * @return String
     */
    public function findColumn($condition, $params, $column)
    {
        if (!empty($column)) $this->builder->setField($column);
        return $this->builder->where($condition, $params)->fetchColumn();
    }

    /**
     * get one column string from table by id
     *
     * @param int $id
     * @param string $column
     * @return String
     */
    public function fetchColumn($id, $column)
    {
        if (!empty($column)) $this->builder->setField($column);
        return $this->builder->where($this->tblName.'.id = ?', array($id))->fetchColumn();
    }

    /**
     * get record from table
     *
     * @param int $rows
     * @param int $start
     * @param string $order
     * @param string $fields
     * @param int $fetchMode
     * @return array
     */
    public function fetchAll($rows = 0, $start = 0, $order = '', $fields = '*', $fetchMode = PDO::FETCH_ASSOC)
    {
        return $this->builder->field($fields)->limit($rows, $start)->orderby($order)->fetchAll(null, $fetchMode);
    }

    /**
     * get one column list from table
     *
     * @param string $fields
     * @param int $rows
     * @param int $start
     * @param string $order
     * @return array
     */
    public function fetchAllUnique($fields = '*', $rows = 0, $start = 0, $order = '')
    {
        return $this->builder->field($fields)->limit($rows, $start)->orderby($order)->fetchAllUnique();
    }

    /**
     * get record from table by condition
     *
     * @param string $condition
     * @param int $rows
     * @param int $start
     * @param string $order
     * @param string $fields
     * @param int $fetchMode
     * @return array
     */
    public function findAll($condition = '', $rows = 0, $start = 0, $order='', $fields = '*', $fetchMode = PDO::FETCH_ASSOC)
    {
        if (is_array($condition)) {
            $where = $condition[0];
            $params = $condition[1];
        } else {
            $where = $condition;
            $params = null;
        }
        return $this->builder->field($fields)->where($where, $params)->orderby($order)->limit($rows, $start)->fetchAll(null, $fetchMode);
    }

    /**
     * get one column list from table by condition
     *
     * @param string $condition
     * @param string $fields
     * @param int $rows
     * @param int $start
     * @param string $order
     * @return array
     */
    public function findAllUnique($condition = '', $fields = '', $rows = 0, $start = 0, $order = '')
    {
        if (is_array($condition)) {
            $where = $condition[0];
            $params = $condition[1];
        } else {
            $where = $condition;
            $params = null;
        }
        return $this->builder->field($fields)->where($where, $params)->orderby($order)->limit($rows, $start)->fetchAllUnique();
    }

    /**
     * get key=>value formatted result from table
     *
     * @param string $condition
     * @param string $fields
     * @param int $rows
     * @param int $start
     * @param string $order
     * @return array
     */
    public function findAllKvPair($condition = '', $fields = '', $rows = 0, $start = 0, $order = '')
    {
        if (is_array($condition)) {
            $where = $condition[0];
            $params = $condition[1];
        } else {
            $where = $condition;
            $params = null;
        }
        return $this->builder->field($fields)->where($where, $params)->orderby($order)->limit($rows, $start)->fetchAllKvPair();
    }

    /**
     * count records
     *
     * @param string $condition
     * @param null $params
     * @param bool $distinct
     * @return int
     */
    public function count($condition = '', $params = null, $distinct = false)
    {
        if (!empty($condition)) {
            $this->builder->where($condition, $params);
        }
        return $this->builder->recordsCount($distinct);
    }

    /**
     * Check if the records exists according to the $condition.
     *
     * @param String $condition
     * @param mixed $params
     * @return boolean
     */
    public function exists($condition = '', $params = null)
    {
        if (!is_array($params)) $params = array($params);
        $cnt = $this->builder->setField('count(*)')->where($condition, $params)->fetchColumn();
        return $cnt > 0 ? true : false;
    }

    /**
     * Check if the records exists according to the $condition. if exists, return the row data.
     * result[0] is a bool value represent exists or not.
     * If exists, result[1] will store the "1st db row" result
     *
     * @param String $condition
     * @param string|array $params
     * @param string $fields
     * @return Array list(exists, row) = Array(0=>true/false, 1=>rowArray/false)
     */
    public function existsRow($condition='', $params = null, $fields = null)
    {
        if (!empty($fields)) $this->builder->setField($fields);
        $row = $this->builder->where($condition, $params)->fetch(null, PDO::FETCH_BOTH);
        $exists = empty($row) ? false : true;
        return array($exists, $row);
    }

    /**
     * return the max Id from current table
     *
     * @return int the max id
     */
    public function maxId()
    {
        return $this->builder->setField('id')->orderby('id DESC')->fetchColumn();
    }

    /**
     * one to one relation.
     *
     * @param String $table table name [and alias] which need to be joined. eg. User as Author
     * @param String $fields the fields you need to retrieve. default is all. E.G. Author.uname as authorUname, Author.nickname as nickname.
     * @param String $foreignKey ForeignKey field name. default is null which will use tableName+Id as its FK. eg. userId, productId
     * @param String $joinType one of the three [inner, left, right]. default is left.
     * @return $this
     */
    public function hasA($table, $fields='', $foreignKey = null, $joinType = 'left')
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

        $foreignKey = $foreignKey ? $foreignKey : lcfirst($tblName).'Id';
        $foreignKey = $this->connection->quoteIdentifier($foreignKey);
        $joinType = $joinType.' JOIN';

        $tblName = $this->connection->quoteIdentifier($tblName);
        $this->builder->join("$tblName $tblAlias", "$this->tblName.$foreignKey=$tblAlias.id", $fields, $joinType);

        return $this;
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    public function debug($connection = null)
    {
        if (is_null($connection)) {
            $connection = $this->connection;
        }
        return $connection->debug();
    }

    public function lastSql($builder = null)
    {
        if (is_null($builder)) {
            $builder = $this->builder;
        }
        return $builder->sql();
    }

    /**
     * return the slave table handler object
     *
     * @return \Ludo\Database\Builders\Builder
     */
    public function tbl()
    {
        return $this->builder;
    }

    public function tblName()
    {
        return $this->tblName;
    }

    public function daoName($trailingDao = true, $lcFirst = false)
    {
        $daoName = get_class($this);
        if (!$trailingDao) {
            $daoName = substr($daoName, 0, strpos($daoName, 'Dao'));
        }
        if ($lcFirst) $daoName[0] = strtolower($daoName[0]);
        return $daoName;
    }

    public function truncate()
    {
        $this->connection->statement('TRUNCATE '.$this->tblName);
    }
}
