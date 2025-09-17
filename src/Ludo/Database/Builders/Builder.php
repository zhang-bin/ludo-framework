<?php

namespace Ludo\Database\Builders;

use Ludo\Database\Connection;
use PDO;


/**
 * Sql builder
 *
 * @package Ludo\Database\Builders
 */
class Builder
{
    /**
     * @var Connection $db Database connection object
     */
    protected Connection $db;

    /**
     * @var string $tableName table name
     */
    protected string $tableName = '';

    /**
     * @var string $tableAlias current table's alias, default is table name without prefix
     */
    protected string $tableAlias = '';

    /**
     * @var array $fields fields part of the select clause, default is '*'
     */
    protected array $fields = [];

    /**
     * @var string $join Join clause
     */
    protected string $join = '';

    /**
     * @var string $where condition
     */
    protected string $where = '';

    /**
     * @var string $having having
     */
    protected string $having = '';

    /**
     * @var array $params params used to replace the placeholder in condition
     */
    protected array $params = [];

    /**
     * @var string $order order by
     */
    protected string $order = '';

    /**
     * @var string $group group by
     */
    protected string $group = '';

    /**
     * @var string $sql current sql clause
     */
    protected string $sql = '';

    /**
     * @var string $userSql sql clause directly assigned by User
     */
    protected string $userSql = '';

    /**
     * @var bool $distinct distinct
     */
    protected bool $distinct = false;

    /**
     * @var string $limit limit rows, start
     */
    protected string $limit = '';

    const string LEFT_JOIN = 'LEFT JOIN';
    const string INNER_JOIN = 'INNER JOIN';
    const string RIGHT_JOIN = 'RIGHT JOIN';

    /**
     * @param Connection $dbObj
     * @param string $tableName table name without prefix
     * @param string $tableAlias alias of table, Default equals table name without prefix
     */
    public function __construct(Connection $dbObj, string $tableName, string $tableAlias = '')
    {
        $this->db = $dbObj;
        $this->tableName = $this->db->getTablePrefix() . $tableName;

        //tableAlias default is the table name without prefix
        $this->tableAlias = $tableAlias ?: $tableName;
    }

    /**
     * Set table Alias
     *
     * @param string $tableAlias Table's alias
     * @return $this
     */
    public function setTableAlias(string $tableAlias): Builder
    {
        $this->tableAlias = $tableAlias;
        return $this;
    }

    /**
     * Set or get sql
     *
     * @param string $sql if empty will return last sql condition
     * @param array $params where parameters
     * @return $this|String
     */
    public function sql(string $sql = '', array $params = []): string|Builder
    {
        if (empty($sql)) {
            return $this->sql;
        } else {
            $this->sql = '';
            $this->userSql = $sql;
            $this->params = $params;
            return $this;
        }
    }

    /**
     * Set the field part of sql clause
     *
     * @param string $fieldName comma separated list: id, User.name, UserType.name
     * @return $this
     */
    public function setField(string $fieldName): Builder
    {
        if (!empty($fieldName)) {
            $this->fields[] = $fieldName;
        }

        return $this;
    }

    /**
     * identical to setField()
     *
     * @param String $fieldName comma separated list: id, User.name, UserType.name
     * @return $this
     */
    public function field(string $fieldName): Builder
    {
        if ($fieldName == '*') {
            $fieldName = $this->tableAlias . '.*';
        }

        return $this->setField($fieldName);
    }

    /**
     * whether to distinct search for the fields.
     *
     * @param bool $distinct whether to distinct rows, default is false;
     * @return $this
     */
    public function distinct(bool $distinct = false): Builder
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * used by $this->join()
     * @param string $fields field part of joined table
     * @return $this
     */
    protected function addJoinField(string $fields): Builder
    {
        $this->fields[] = $fields;
        return $this;
    }

    /**
     * join a table, This function can be multiple called and each call will be concatenated.
     *
     * @param string $table the table will be joined, which can have alias like "user u" or "user as u"
     * @param string $on on condition
     * @param string $fields the fields came from the joined table
     * @param string $join join type: LdTable::LEFT_JOIN OR LdTable::RIGHT_JOIN OR LdTable::INNER_JOIN.
     * @return $this
     */
    public function join(string $table, string $on = '', string $fields = '', string $join = self::INNER_JOIN): Builder
    {
        $as = $table;
        //if $table have ' ' which means $table have an alias,
        //so replace the as if you have and separate the table name and alias name.
        if (strchr($table, ' ')) {
            $tmp = explode(' ', str_replace(' as ', ' ', $table));
            $table = $tmp[0];
            $as = $tmp[1];
        }

        $table = $this->db->quoteIdentifier($this->db->getTablePrefix() . $table);

        if ($fields) {
            $this->addJoinField($fields);
        }

        $on = $on ? 'ON ' . $on : '';

        $this->join .= " $join $table $as $on ";
        return $this;
    }

    /**
     * left join a table, This function can be multiple called and each call will be concatenated.
     *
     * @param string $table the table will be joined, which can have alias like "user u" or "user as u"
     * @param string $on on condition
     * @param string $fields the fields came from the joined table
     * @return $this
     */
    public function leftJoin(string $table, string $on = '', string $fields = ''): Builder
    {
        return $this->join($table, $on, $fields, self::LEFT_JOIN);
    }

    /**
     * Right join a table, This function can be multiple called and each call will be concatenated.
     *
     * @param string $table the table will be joined, which can have alias like "user u" or "user as u"
     * @param string $on on condition
     * @param string $fields the fields came from the joined table
     * @return $this
     */
    public function rightJoin(string $table, string $on = '', string $fields = ''): Builder
    {
        return $this->join($table, $on, $fields, self::RIGHT_JOIN);
    }

    /**
     * inner join a table, This function can be multiple called and each call will be concatenated.
     *
     * @param string $table the table will be joined, which can have alias like "user u" or "user as u"
     * @param string $on on condition
     * @param string $fields the fields came from the joined table
     * @return $this
     */
    public function innerJoin(string $table, string $on = '', string $fields = ''): Builder
    {
        return $this->join($table, $on, $fields);
    }

    /**
     * set condition part in query clause
     *
     * @param string $condition e.g. 'field1=1 & tableAlias.field3=3' or 'field1=? & tableAlias.field3=?' or
     *                               'field1=:name & tableAlias.field3=:user'
     * @param array $params
     * @return $this
     */
    public function where(string $condition, array $params = []): Builder
    {
        if (!empty($condition)) {
            $this->where = 'WHERE ' . $condition;
            $this->params = $params;
        }
        return $this;
    }

    /**
     * set condition part in query clause
     *
     * @param string $condition e.g. 'field1=1 & tableAlias.field3=3' or 'field1=? & tableAlias.field3=?' or
     *                               'field1=:name & tableAlias.field3=:user'
     * @param array $params
     * @return $this
     */
    public function having(string $condition, array $params = []): Builder
    {
        $this->having = 'HAVING ' . $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * set order part in query clause
     * @param string $order : e.g. id DESC
     * @return $this
     */
    public function orderBy(string $order): Builder
    {
        $this->order = $order;
        return $this;
    }

    /**
     * set group part in query clause
     *
     * @param String $group e.g. 'field1'
     * @return $this
     */
    public function groupBy(string $group): Builder
    {
        $this->group = $group;
        return $this;
    }

    /**
     * set group part in query clause
     *
     * @param int $rows
     * @param int $start
     * @return $this
     */
    public function limit(int $rows = 0, int $start = 0): Builder
    {
        if (empty($rows)) {
            $this->limit = '';
        } else {
            $this->limit = "LIMIT $rows OFFSET $start";
        }
        return $this;
    }

    /**
     * construct all the given information to a sql clause. often used by read-only query.
     * @param bool $return true: return the sql clause (Default is true). false: assign sql clause to this->sql.
     * @return string|Builder
     */
    protected function constructSql(bool $return = true): Builder|string|static
    {
        if (empty($this->userSql)) {
            $distinct = $this->distinct ? 'DISTINCT' : '';

            $groupBy = '';
            if (!empty($this->group)) {
                $groupBy = 'GROUP BY ' . $this->group;
                if (!empty($this->having)) $groupBy .= ' ' . $this->having;
            }
            $order = !empty($this->order) ? 'ORDER BY ' . $this->order : '';

            if (empty($this->fields)) {
                $fields = $this->tableAlias . '.*';
            } else {
                $fields = implode(',', $this->fields);
            }
            $sql = "SELECT $distinct $fields FROM {$this->db->quoteIdentifier($this->tableName)} $this->tableAlias $this->join $this->where $groupBy $order $this->limit";
        } else {
            $sql = $this->userSql;
        }

        $this->reset();
        if ($return) {
            return $sql;
        } else {
            $this->sql = $sql;
            return $this;
        }
    }

    /**
     * do a query directly, which will return a result
     *
     * @param int $fetchMode
     * @param mixed|null $fetchArgument
     * @return array
     */
    public function select(int $fetchMode = PDO::FETCH_ASSOC, mixed $fetchArgument = null): array
    {
        $this->db->setFetchMode($fetchMode);
        $this->db->setFetchArgument($fetchArgument);
        return $this->db->select($this->constructSql(), $this->params);
    }

    /**
     * get one row from table into an array
     * @param int $fetchMode PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH
     *
     * @return array represent one row in a table, or false if failure
     */
    public function fetch(int $fetchMode = PDO::FETCH_ASSOC): array
    {
        $this->limit(1);
        $this->db->setFetchMode($fetchMode);

        $result = $this->db->selectOne($this->constructSql(), $this->params);

        (false === $result) && ($result = []);
        return $result;
    }

    /**
     * get all rows from table into an 2D array
     *
     * @param int $fetchMode Controls the contents of the returned array.
     * Defaults to PDO::FETCH_BOTH. Other useful options is:
     * PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE: To fetch only the unique values of a single column from the result set
     * PDO::FETCH_COLUMN|PDO::FETCH_GROUP: To return an associative array grouped by the values of a specified column
     * @return array represents a table
     */
    public function fetchAll(int $fetchMode = PDO::FETCH_ASSOC): array
    {
        return $this->select($fetchMode);
    }

    /**
     * get the same column in each rows from table into an 1D array.
     * e.g. select col1 from table limit 0,3.
     * will return: array(row1_col1, row2_col1, row3_col1);
     *
     * @return array represents a table
     */
    public function fetchAllUnique(): array
    {
        return $this->select(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, 0);
    }

    /**
     * get the same column in each rows from table into an 1D array.
     * note:
     *
     * @return array represents a table
     * @example
     * <pre>
     * select col1, col2 from table limit 0,3. \n
     * will return: array(row1_col1=>row1_col2, row2_col1=>row2_col2, row3_col1=>row3_col2);
     * </pre>
     *
     */
    public function fetchAllKvPair(): array
    {
        return $this->select(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns a single column from the next row of a result set
     *
     * @return mixed Returns a single column from the next row of a result set or FALSE if there are no more rows.
     */
    public function fetchColumn(): mixed
    {
        return $this->db->selectColumn($this->constructSql(), $this->params);
    }

    /**
     * get the records count
     *
     * @param string $distinctFields which field(s) for identifying distinct.
     * @return int the record count
     */
    public function recordsCount(string $distinctFields = ''): int
    {
        $this->fields[] = $distinctFields ? "count(DISTINCT $distinctFields)" : 'count(*)';
        return $this->fetchColumn();
    }

    /**
     * insert a new record into table
     *
     * @param array $arr key is the field name and value is the field value
     *              array(  'field1_name' => 'value',
     *                      'field2_name' => 'value',
     *                      ...);
     * @return int Last insert id if insert successful, else SqlException will be thrown
     */
    public function insert(array $arr): int
    {
        if (empty($arr)) {
            return false;
        }

        $comma = '';
        $setFields = '(';
        $setValues = '(';
        $params = [];
        foreach ($arr as $key => $value) {
            $params[] = $value;
            $key = $this->db->quoteIdentifier($key);
            $setFields .= "$comma$key";
            $setValues .= $comma . '?';
            $comma = ',';
        }
        $setFields .= ')';
        $setValues .= ')';

        $sql = "INSERT INTO  {$this->db->quoteIdentifier($this->tableName)} $setFields values $setValues";
        $this->db->insert($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * update a record in the table
     *
     * @param array $arr key is the field name and value is the field value
     *              array(  'field1_name' => 'value',
     *                      'field2_name' => 'value',
     *                      ...);
     * @param ?string $condition The query condition. with following format:<br />
     *        Array:  array('id=? and uname=?', array(2, 'test')); //
     *
     * @param array $params The query parameters
     *
     * @return int row number if insert successful, else SqlException will be thrown
     */
    public function update(array $arr, ?string $condition = null, array $params = []): int
    {
        if (empty($arr)) {
            return false;
        }

        $comma = '';
        $setFields = '';
        $sqlParams = [];
        foreach ($arr as $key => $value) {
            $sqlParams[] = $value;
            $key = $this->db->quoteIdentifier($key);
            $setFields .= "$comma $key=?";
            $comma = ',';
        }
        $sql = "UPDATE {$this->db->quoteIdentifier($this->tableName)} set $setFields";

        if (!empty($condition)) {
            $sql .= ' WHERE ' . $condition;
            $sqlParams = array_merge($sqlParams, $params);
        }

        return $this->db->update($sql, $sqlParams);
    }

    /**
     * delete record from table
     *
     * @param string $condition The query condition. with following format:<br />
     *        String: 'id=2 and uname="jack"' or 'id=? and uname=?' or 'id=:id and uname=:uname'
     * @param array|string|null $params params which will be used in prepared statement, with following format: <br />
     *        String: if you just need one parameter in above prepared statement. e.g. '1111'
     *        Array: array(2, 'jack') or array(':id'=>2, ':uname'=>'jack')
     *
     * @return int row nums if insert successful, else SqlException will be thrown
     * @access public
     */
    public function delete(string $condition = '', array|string $params = null): int
    {
        $sql = "DELETE FROM {$this->db->quoteIdentifier($this->tableName)}";

        if (!empty($condition)) {
            if (!is_null($params) && !is_array($params)) { //using prepared statement.
                $params = [$params];
            }
            $sql .= ' WHERE ' . $condition;
        }

        return $this->db->delete($sql, $params);
    }

    /**
     * reset some data member of LdTable which used to construct a sql clause
     * this method usually called after an DataBase query finished (e.g. $this->select();)
     */
    protected function reset(): void
    {
        $this->fields = [];
        $this->join = '';
        $this->where = '';
        $this->having = '';
        $this->order = '';
        $this->group = '';
        $this->distinct = false;
        $this->userSql = '';
        $this->limit = '';
    }

    /**
     * execute an insert/update/delete sql clause directly,
     * @param string $sql sql clause
     * @param array $params
     * @return int affected rows
     */
    public function exec(string $sql, array $params = []): int
    {
        return $this->db->affectingStatement($sql, $params);
    }
}
