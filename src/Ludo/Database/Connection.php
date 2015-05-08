<?php
namespace Ludo\Database;

use PDO;
use Closure;

class Connection
{

	/**
	 * The active PDO connection.
	 *
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * The active PDO connection used for reads.
	 *
	 * @var PDO
	 */
	protected $readPdo;

	/**
	 * All of the queries run against the connection.
	 *
	 * @var array
	 */
	protected $queryLog = array();

	/**
	 * The default fetch mode of the connection.
	 *
	 * @var int
	 */
	protected $fetchMode = PDO::FETCH_ASSOC;

	/**
	 * The default fetch argument of the connection.
	 *
	 * @var int
	 */
	protected $fetchArgument = null;

	/**
	 * The name of the connected database.
	 *
	 * @var string
	 */
	protected $database;

	/**
	 * The table prefix for the connection.
	 *
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * The database connection configuration options.
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Create a new database connection instance.
	 *
	 * @param  PDO     $pdo
	 * @param  string  $database
	 * @param  string  $tablePrefix
	 * @param  array   $config
	 */
	public function __construct(PDO $pdo, $database = '', $tablePrefix = '', array $config = array())
    {
		$this->pdo = $pdo;
		$this->database = $database;
		$this->tablePrefix = $tablePrefix;
		$this->config = $config;
	}

	/**
	 * Run a select statement and return a single column result.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return array
	 */
	public function selectColumn($query, $params = array())
    {
		return $this->run($query, $params, function($me, $query, $params) {
			/**
			 * @var Connection $me
			 */
			$statement = $me->getReadPdo()->prepare($query);
			$statement->execute($params);
			return $statement->fetchColumn();
		});
	}

	/**
	 * Run a select statement and return a single result.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return array
	 */
	public function selectOne($query, $params = array())
    {
		return $this->run($query, $params, function($me, $query, $params) {
			/**
			 * @var Connection $me
			 */
			$statement = $me->getReadPdo()->prepare($query);
			$statement->execute($params);
			return $statement->fetch($me->getFetchMode());
		});
	}

	/**
	 * Run a select statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return array
	 */
	public function select($query, $params = array())
    {
		return $this->run($query, $params, function($me, $query, $params) {
			/**
			 * @var Connection $me
			 */
			$statement = $me->getReadPdo()->prepare($query);
			$statement->execute($params);
			if (is_null($me->getFetchArgument())) {
				return $statement->fetchAll($me->getFetchMode());
			} else {
				return $statement->fetchAll($me->getFetchMode(), $me->getFetchArgument());
			}
		});
	}

	/**
	 * Run an insert statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return bool
	 */
	public function insert($query, $params = array())
    {
		return $this->statement($query, $params);
	}

	/**
	 * Run an update statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return int affected row
	 */
	public function update($query, $params = array())
    {
		return $this->affectingStatement($query, $params);
	}

	/**
	 * Run a delete statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return int affected row
	 */
	public function delete($query, $params = array())
    {
		return $this->affectingStatement($query, $params);
	}

	/**
	 * Execute an SQL statement and return the boolean result.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return bool
	 */
	public function statement($query, $params = array())
    {
		return $this->run($query, $params, function($me, $query, $params) {
			/**
			 * @var Connection $me
			 */
			return $me->getPdo()->prepare($query)->execute($params);
		});
	}

	/**
	 * Run an SQL statement and get the number of rows affected.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @return int
	 */
	public function affectingStatement($query, $params = array())
    {
		return $this->run($query, $params, function($me, $query, $params) {
			/**
			 * @var Connection $me
			 */
			$statement = $me->getPdo()->prepare($query);
			$statement->execute($params);
			return $statement->rowCount();
		});
	}

	/**
	 * Run a raw, unprepared query against the PDO connection.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function unprepared($query)
    {
		return $this->run($query, array(), function($me, $query) {
			/**
			 * @var Connection $me
			 */
			return (bool) $me->getPdo()->exec($query);
		});
	}

	/**
	 * get last insert id
	 *
	 * @param string $name
	 * @return string insert id
	 */
	public function lastInsertId($name = 'id')
    {
		return $this->getReadPdo()->lastInsertId($name);
	}


	/**
	 * Execute a Closure within a transaction.
	 *
	 * @param  Closure  $callback
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	public function transaction(Closure $callback)
    {
		$this->beginTransaction();

		try {
			$result = $callback($this);
			$this->commit();
		} catch (\Exception $e) {
			$this->rollBack();
			throw $e;
		}
		return $result;
	}

	/**
	 * Start a new database transaction.
	 *
	 * @return void
	 */
	public function beginTransaction()
    {
		$this->pdo->beginTransaction();
	}

	/**
	 * Commit the active database transaction.
	 *
	 * @return void
	 */
	public function commit()
    {
		$this->pdo->commit();
	}

	/**
	 * Rollback the active database transaction.
	 *
	 * @return void
	 */
	public function rollBack()
    {
		$this->pdo->rollBack();
	}

	/**
	 * Run a SQL statement and log its execution context.
	 *
	 * @param  string   $query
	 * @param  array    $params
	 * @param  Closure  $callback
	 * @return mixed
	 *
	 * @throws QueryException
	 */
	protected function run($query, $params, Closure $callback)
    {
		$start = microtime(true);

		// To execute the statement, we'll simply call the callback, which will actually
		// run the SQL against the PDO connection. Then we can calculate the time it
		// took to execute and log the query SQL, bindings and time in our memory.
		$err = '';
		try {
			$result = $callback($this, $query, $params);
		    return $result;
		} catch (\Exception $e) {
			$err = $e->getMessage();
            $time = '['.date('Y-m-d H:i:s').']    ';
            error_log($time.$e->getTraceAsString());
			throw new QueryException($query, (array)$params, $e);
		} finally {
            // Once we have run the query we will calculate the time that it took to run and
            // then log the query, bindings, and execution time so we will report them on
            // the event that the developer needs them. We'll log time in milliseconds.
            $time = $this->getElapsedTime($start);

            $this->logQuery($query, $params, $time, $err);
        }
	}

	/**
	 * Log a query in the connection's query log.
	 *
	 * @param  string  $query
	 * @param  array   $params
	 * @param  $time
	 * @param  $err
	 * @return void
	 */
	public function logQuery($query, $params, $time = null, $err = null)
    {
		if (!DEBUG) return;
		$this->queryLog[] = compact('query', 'params', 'time', 'err');
	}

	/**
	 * Get the elapsed time since a given starting point.
	 *
	 * @param  int    $start
	 * @return float
	 */
	protected function getElapsedTime($start)
    {
		return microtime(true) - $start;
	}

	/**
	 * Get the current PDO connection.
	 *
	 * @return PDO
	 */
	public function getPdo()
    {
		return $this->pdo;
	}

	/**
	 * Get the current PDO connection used for reading.
	 *
	 * @return PDO
	 */
	public function getReadPdo()
    {
		return $this->readPdo ?: $this->pdo;
	}

	/**
	 * Set the PDO connection.
	 *
	 * @param  PDO  $pdo
	 * @return \Ludo\Database\Connection
	 */
	public function setPdo(PDO $pdo)
    {
		$this->pdo = $pdo;
		return $this;
	}

	/**
	 * Set the PDO connection used for reading.
	 *
	 * @param  PDO  $pdo
	 * @return \Ludo\Database\Connection
	 */
	public function setReadPdo(PDO $pdo)
    {
		$this->readPdo = $pdo;
		return $this;
	}

	/**
	 * Get the database connection name.
	 *
	 * @return string|null
	 */
	public function getName()
    {
		return $this->getConfig('name');
	}

	/**
	 * Get an option from the configuration options.
	 *
	 * @param  string  $option
	 * @return mixed
	 */
	public function getConfig($option)
    {
		return array_get($this->config, $option);
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string
	 */
	public function getDriverName()
    {
		return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * Get the default fetch mode for the connection.
	 *
	 * @return int
	 */
	public function getFetchMode()
    {
		return $this->fetchMode;
	}

	/**
	 * Set the default fetch mode for the connection.
	 *
	 * @param  int  $fetchMode
	 * @return int
	 */
	public function setFetchMode($fetchMode)
    {
		$this->fetchMode = $fetchMode;
	}

	/**
	 * Get the default fetch argument for the connection.
	 *
	 * @return int
	 */
	public function getFetchArgument()
    {
		return $this->fetchArgument;
	}

	/**
	 * Set the default fetch argument for the connection.
	 *
	 * @param  int  $fetchArgument
	 * @return int
	 */
	public function setFetchArgument($fetchArgument)
    {
		$this->fetchArgument = $fetchArgument;
	}

	/**
	 * Get the name of the connected database.
	 *
	 * @return string
	 */
	public function getDatabaseName()
    {
		return $this->database;
	}

	/**
	 * Set the name of the connected database.
	 *
	 * @param  string  $database
	 * @return string
	 */
	public function setDatabaseName($database)
    {
		$this->database = $database;
	}

	/**
	 * Get the table prefix for the connection.
	 *
	 * @return string
	 */
	public function getTablePrefix()
    {
		return $this->tablePrefix;
	}

    /**
     * quote identifier
     *
     * @param $str
     * @return string
     */
	function quoteIdentifier($str)
    {
		$str = trim($str, '`');
		return "`{$str}`";
	}

	/**
	 * print query log
	 *
	 * @return string
	 */
	public function debug()
    {
		if (!DEBUG) return null;
		$totalProcessTime = 0;
		$totalSQL = 0;

		$str = json_encode($this->config).'<br />';
		$str .= <<<EOF
<table id="debugtable" width="100%" border="0" cellspacing="1" style="background:#DDDDF0;word-break: break-all;">
	<tr style="background:#A5BDD8;height:30px;Color:White;">
		<th>Query</th>
		<th width=100>Params</th>
		<th width=50>Error</th>
		<th width=100>ProcessTime</th>
	 </tr>
EOF;
		foreach($this->queryLog as $log) {
			$str .= '<tr style="background:#EEEEEE;Height:25px;Text-Align:center;">
						<td align=left>'.HtmlSpecialChars($log['query']).'</td>
						<td align=left>'.var_export($log['params'], true).'</td>
						<td>'.@$log['err'].'</td>
						<td>'.sprintf('%.4f',$log['time']).'</td>
					 </tr>';
			$totalProcessTime += (double)$log["time"];
			$totalSQL++;
		}

		$str .= "<tr style='background:#EEEEEE;Height:30px;text-align:center'>
					<td colspan=5>
						Total execute queries: ". $totalSQL
			. "&nbsp;Total ProcessTime:"
			. sprintf('%.4f',$totalProcessTime)
			. "</td>
				 </tr>\n";

		$str .= "</table>";

		return $str;
	}
}
