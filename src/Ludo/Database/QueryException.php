<?php
namespace Ludo\Database;

use PDOException;

class QueryException extends PDOException
{

	/**
	 * The SQL for the query.
	 *
	 * @var string
	 */
	protected $sql;

	/**
	 * The params for the query.
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * Create a new query exception instance.
	 *
	 * @param  string  $sql
	 * @param  array  $params
	 * @param  \Exception $previous
	 */
	public function __construct($sql, array $params, $previous)
    {
		$this->sql = $sql;
		$this->params = $params;
		$this->previous = $previous;
		$this->code = $previous->getCode();
		$this->message = $this->formatMessage($sql, $params, $previous);

		if ($previous instanceof PDOException) {
			$this->errorInfo = $previous->errorInfo;
		}
	}

	/**
	 * Format the SQL error message.
	 *
	 * @param  string  $sql
	 * @param  array  $params
	 * @param  \Exception $previous
	 * @return string
	 */
	protected function formatMessage($sql, $params, $previous)
    {
		return $previous->getMessage().' (SQL: '.str_replace_array('\?', $params, $sql).')';
	}

	/**
	 * Get the SQL for the query.
	 *
	 * @return string
	 */
	public function getSql()
    {
		return $this->sql;
	}

	/**
	 * Get the params for the query.
	 *
	 * @return array
	 */
	public function getParams()
    {
		return $this->params;
	}
}
