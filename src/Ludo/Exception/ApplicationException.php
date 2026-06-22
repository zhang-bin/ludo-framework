<?php

namespace Ludo\Exception;

use Exception;
use Throwable;

class ApplicationException extends Exception
{
    public function __construct($message, $code, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}