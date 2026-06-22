<?php

namespace Ludo\Exception;

use Exception;
use Throwable;

class ApplicationException extends Exception
{
    protected string|int $errorCode;

    public function __construct(string $message, string|int $code = 0, ?Throwable $previous = null)
    {
        $this->errorCode = $code;
        parent::__construct($message, is_int($code) ? $code : 0, $previous);
    }

    public function getErrorCode(): string|int
    {
        return $this->errorCode;
    }
}