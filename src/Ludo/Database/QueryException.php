<?php

namespace Ludo\Database;

use PDOException;
use Exception;

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
     * @param string $sql
     * @param array $params
     * @param Exception $previous
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
     * @param string $sql
     * @param array $params
     * @param Exception $previous
     * @return string
     */
    protected function formatMessage($sql, $params, $previous): string
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
