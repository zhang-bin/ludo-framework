<?php

namespace Ludo\Database\Builders;

use Ludo\Database\Connection;
use PDO;
use phpDocumentor\Reflection\Types\This;

class Builder
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var string  table name
     */
    protected $tableName = '';

    /**
     * @var string current table's alias, default is table name without prefix
     */
    protected $tableAlias = '';

    /**
     * @var array fields part of the select clause, default is '*'
     */
    protected $fields = array();

    /**
     * @var string Join clause
     */
    protected $join = '';

    /**
     * @var string condition
     */
    protected $where = '';

    /**
     * @var string having
     */
    protected $having = '';

    /**
     * @var array params used to replace the placeholder in condition
     */
    protected $params = array();

    /**
     * @var string order by
     */
    protected $order = '';

    /**
     * @var string group by
     */
    protected $group = '';

    /**
     * @var string current sql clause
     */
    protected $sql = '';

    /**
     * @var string sql clause directly assigned by User
     */
    protected $userSql = '';

    /**
     * @var bool distinct
     */
    protected $distinct = false;

    /**
     * @var string limit rows, start
     */
    protected $limit = '';

    const LEFT_JOIN = 'LEFT JOIN';
    const INNER_JOIN = 'INNER JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';

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
        $this->tableAlias = $tableAlias ? $tableAlias : $tableName;
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
     * set or get sql
     *
     * @param string $sql if empty will return last sql condition
     * @param string|array $params
     * @return $this|String
     */
    public function sql(string $sql = '', $params = null)
    {
        if (!empty($sql)) {
            $this->sql = '';
            $this->userSql = $sql;
            $this->params = $this->autoArr($params);
            return $this;
        } else {
            return $this->sql;
        }
    }

    /**
     * set the field part of sql clause
     *
     * @param string $fieldName comma separated list: id, User.name, UserType.id
     * @return $this
     */
    public function setField(string $fieldName): Builder
    {
        if (!empty($fieldName)) {
            array_push($this->fields, $fieldName);
        }

        return $this;
    }

    /**
     * identical to setField()
     *
     * @param String $fieldName comma separated list: id, User.name, UserType.id
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
        array_push($this->fields, $fields);
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
        //if $table have ' ' which means $table have a alias,
        //so replace the as if have and separate the table name and alias name.
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

        $this->join .= " {$join} {$table} {$as} {$on} ";
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
        return $this->join($table, $on, $fields, self::INNER_JOIN);
    }

    /**
     * set condition part in query clause
     *
     * @param string $condition e.g. 'field1=1 & tableAlias.field3=3' or 'field1=? & tableAlias.field3=?' or
     *                               'field1=:name & tableAlias.field3=:user'
     * @param array|null $params
     * @return $this
     */
    public function where(string $condition, $params = null): Builder
    {
        if (!empty($condition)) {
            $this->where = 'WHERE ' . $condition;
            $this->params = $this->autoArr($params);
        }
        return $this;
    }

    /**
     * set condition part in query clause
     *
     * @param string $condition e.g. 'field1=1 & tableAlias.field3=3' or 'field1=? & tableAlias.field3=?' or
     *                               'field1=:name & tableAlias.field3=:user'
     * @param array|null $params
     * @return $this
     */
    public function having(string $condition, $params = null): Builder
    {
        $this->having = 'HAVING ' . $condition;
        $this->params = empty($this->params) ? $this->autoArr($params) : array_merge($this->params, $this->autoArr($params));
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
            $this->limit = "LIMIT {$rows} OFFSET {$start}";
        }
        return $this;
    }

    /**
     * construct all the given information to a sql clause. often used by read-only query.
     * @param bool $return true: return the sql clause (Default is true). false: assign sql clause to this->sql.
     * @return mixed
     */
    protected function constructSql(bool $return = true)
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
            $sql = "SELECT $distinct $fields FROM {$this->db->quoteIdentifier($this->tableName)} {$this->tableAlias} {$this->join} {$this->where} {$groupBy} {$order} {$this->limit}";
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
     * do an query directly, which will return a result
     *
     * @param array/string $params
     * @param int $fetchMode
     * @param mixed $fetchArgument
     * @return array
     */
    public function select($multi_call_params = NULL, int $fetchMode = PDO::FETCH_ASSOC, $fetchArgument = null): array
    {
        # 1. A multi-call means that sql have been prepared to do multiple call with different params.
        # 2. if $multi_call_params is null, means this is an once-call.
        # 	 once-call does not exist this->sql.
        # 3. if $multi_call_params is not null, means this is an multi-call.
        # 	 this->sql only exists when this is an multi-call

        $this->db->setFetchMode($fetchMode);
        $this->db->setFetchArgument($fetchArgument);
        if (is_null($multi_call_params)) {//once-call, this->sql have no value
            return $this->db->select($this->constructSql(), $this->params);
        } else { //multiple-call:
            if (empty($this->sql)) $this->constructSql(false);
            return $this->db->select($this->sql, $this->autoArr($multi_call_params));
        }
    }

    /**
     * get one row from table into an array
     * @param string|array $multi_call_params params used for multi call, assign only if you wanna using multi-call
     *    A multi-call means that sql have been prepared to do multiple call with different params.
     *   if $multi_call_params is not null, means this is an multi-call.
     * @param int $fetchMode PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH
     *
     * @return array|bool represent one row in a table, or false if failure
     */
    public function fetch($multi_call_params = NULL, int $fetchMode = PDO::FETCH_ASSOC)
    {
        $this->limit(1);
        $this->db->setFetchMode($fetchMode);
        if (is_null($multi_call_params)) {//once-call, this->sql have no value
            return $this->db->selectOne($this->constructSql(), $this->params);
        } else { //multiple-call:
            if (empty($this->sql)) $this->constructSql(false);
            return $this->db->selectOne($this->sql, $this->autoArr($multi_call_params));
        }
    }

    /**
     * get all rows from table into an 2D array
     *
     * @param string|array $multi_call_params params used for multi call, assign only if you wanna using multi-call
     *    A multi-call means that sql have been prepared to do multiple call with different params.
     *   if $multi_call_params is not null, means this is an multi-call.
     * @param int $fetchMode Controls the contents of the returned array.
     * Defaults to PDO::FETCH_BOTH. Other useful options is:
     * PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE: To fetch only the unique values of a single column from the result set
     * PDO::FETCH_COLUMN|PDO::FETCH_GROUP: To return an associative array grouped by the values of a specified column
     * @return array represents an table
     */
    public function fetchAll($multi_call_params = null, int $fetchMode = PDO::FETCH_ASSOC)
    {
        return $this->select($multi_call_params, $fetchMode);
    }

    /**
     * get the same column in each rows from table into an 1D array.
     * eg. select col1 from table limit 0,3.
     * will return: array(row1_col1, row2_col1, row3_col1);
     *
     * @param string|array $multi_call_params params used for multi call, assign only if you wanna using multi-call
     *    A multi-call means that sql have been prepared to do multiple call with different params.
     *   if $multi_call_params is not null, means this is an multi-call.
     * @return array represents an table
     */
    public function fetchAllUnique($multi_call_params = null)
    {
        if (PHP_VERSION_ID >= 70000) {
            return $this->select($multi_call_params, PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, 0);
        } else {
            $data = $this->select($multi_call_params, PDO::FETCH_COLUMN);
            $result = array();
            foreach ($data as $datum) {
                $result[$datum] = $datum;
            }
            return $result;
        }
    }

    /**
     * get the same column in each rows from table into an 1D array.
     * note:
     *
     * @param string|array $multi_call_params params used for multi call, assign only if you wanna using multi-call
     *    A multi-call means that sql have been prepared to do multiple call with different params.
     *   if $multi_call_params is not null, means this is an multi-call.
     * @return array represents an table
     * @example
     * <pre>
     * select col1, col2 from table limit 0,3. \n
     * will return: array(row1_col1=>row1_col2, row2_col1=>row2_col2, row3_col1=>row3_col2);
     * </pre>
     *
     */
    public function fetchAllKvPair($multi_call_params = null)
    {
        return $this->select($multi_call_params, PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns a single column from the next row of a result set
     *
     * @param string|array $multi_call_params params used for multi call, assign only if you wanna using multi-call
     *    A multi-call means that sql have been prepared to do multiple call with different params.
     *   if $multi_call_params is not null, means this is an multi-call.
     * @return mixed Returns a single column from the next row of a result set or FALSE if there are no more rows.
     */
    public function fetchColumn($multi_call_params = null)
    {
        if (is_null($multi_call_params)) {//once-call, this->sql have no value
            return $this->db->selectColumn($this->constructSql(), $this->params);
        } else { //multiple-call:
            if (empty($this->sql)) $this->constructSql(false);
            return $this->db->selectColumn($this->sql, $this->autoArr($multi_call_params));
        }
    }

    /**
     * get the records count
     *
     * @param string $distinctFields which field(s) for identifying distinct.
     * @return int the record count
     */
    public function recordsCount(string $distinctFields = ''): int
    {
        array_push($this->fields, $distinctFields ? "count(DISTINCT {$distinctFields})" : 'count(*)');
        return $this->fetchColumn();
    }

    /**
     * insert a new record into table
     *
     * @param array $arr key is the field name and value is the field value
     *              array(  'field1_name' => 'value',
     *                      'field2_name' => 'value',
     *                      ...
     *                    );
     * @return int Last insert id if insert successful, else SqlException will be throwed
     */
    public function insert(array $arr): int
    {
        if (empty($arr)) {
            return false;
        }

        $comma = '';
        $setFields = '(';
        $setValues = '(';
        $params = array();
        foreach ($arr as $key => $value) {
            $params[] = $value;
            $key = $this->db->quoteIdentifier($key);
            $setFields .= "{$comma}{$key}";
            $setValues .= $comma . '?';
            $comma = ',';
        }
        $setFields .= ')';
        $setValues .= ')';

        $sql = "INSERT INTO  {$this->db->quoteIdentifier($this->tableName)} {$setFields} values {$setValues}";
        $this->db->insert($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * update a record in the table
     *
     * @param array $arr key is the field name and value is the field value
     *              array(  'field1_name' => 'value',
     *                      'field2_name' => 'value',
     *                      ...
     *                    );
     * @param string|array $condition The query condition. with following format:<br />
     *        String: 'id=2 and username="test"'
     *        Array:  array('id=? and uname=?', array(2, 'test')); //
     *
     * @return int row number if insert successful, else SqlException will be throw
     */
    public function update(array $arr, string $condition = ''): int
    {
        if (empty($arr)) {
            return false;
        }

        $comma = '';
        $setFields = '';
        $params = array();
        foreach ($arr as $key => $value) {
            $params[] = $value;
            $key = $this->db->quoteIdentifier($key);
            $setFields .= "{$comma} {$key}=?";
            $comma = ',';
        }
        $sql = "UPDATE {$this->db->quoteIdentifier($this->tableName)} set {$setFields}";

        if (!empty($condition)) {
            if (is_array($condition)) {
                $sql .= ' WHERE ' . $condition[0];
                $params = array_merge($params, $this->autoArr($condition[1]));
            } else {
                $sql .= ' WHERE ' . $condition;
                $params = null;
            }
        }

        return $this->db->update($sql, $params);
    }

    /**
     * delete record from table
     *
     * @param string $condition The query condition. with following format:<br />
     *        String: 'id=2 and uanme="libok"' or 'id=? and uname=?' or 'id=:id and uname=:uname'
     * @param string|array $params params which will be used in prepared statement, with following format: <br />
     *        String: if you just need one parameter in above prepared statement. e.g. '1111'
     *        Array: array(2, 'libok') or array(':id'=>2, ':uname'=>'libok')
     *
     * @return int row nums if insert successful, else SqlException will be throwed
     * @access public
     */
    public function delete(string $condition = '', $params = null): int
    {
        $sql = "DELETE FROM {$this->db->quoteIdentifier($this->tableName)}";

        if (!empty($condition)) {
            if (!is_null($params) && !is_array($params)) { //using prepared statement.
                $params = array($params);
            }
            $sql .= ' WHERE ' . $condition;
        }

        return $this->db->delete($sql, $params);
    }

    /**
     * reset some data member of LdTable which used to construct a sql clause
     * this method usually called after an DataBase query finished (e.g. $this->select();)
     */
    protected function reset()
    {
        $this->fields = array();
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
     * @param Mixed $params
     * @return int affected rows
     */
    public function exec(string $sql, $params = null): int
    {
        if (func_num_args() == 2) {
            $params = $this->autoArr($params);
        } else {
            $params = func_get_args();
            array_shift($params);
        }
        return $this->db->affectingStatement($sql, $params);
    }

    /**
     * just for inner use to auto wrap any param to an array.
     *
     * @param string|array $params
     * @return array
     */
    protected function autoArr($params)
    {
        if (!is_null($params) && !is_array($params)) {
            $params = array($params);
        }
        return $params;
    }
}
