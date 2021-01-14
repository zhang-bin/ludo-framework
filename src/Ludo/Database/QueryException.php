<?php

namespace Ludo\Database;

use PDOException;
use Exception;


/**
 * Query Exception
 *
 * @package Ludo\Database
 */
class QueryException extends PDOException
{
    /**
     * @var string $sql sql statement
     */
    protected string $sql;

    /**
     * @var array sql parameters
     */
    protected array $params;

    /**
     * Create a new query exception instance.
     *
     * @param string $sql sql statement
     * @param array $params where parameters
     * @param Exception $previous sql exception
     */
    public function __construct(string $sql, array $params, Exception $previous)
    {
        parent::__construct('', 0, $previous);


        $this->sql = $sql;
        $this->params = $params;
        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($sql, $params, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message.
     *
     * @param string $sql sql statement
     * @param array $params where parameters
     * @param Exception $previous sql exception
     * @return string
     */
    protected function formatMessage(string $sql, array $params, Exception $previous): string
    {
        return $previous->getMessage() . ' (SQL: ' . str_replace_array('\?', $params, $sql) . ')';
    }

    /**
     * Get the SQL for the query.
     *
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Get the params for the query.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
