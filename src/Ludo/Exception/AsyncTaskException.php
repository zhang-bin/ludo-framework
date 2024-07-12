<?php

namespace Ludo\Exception;

use Exception;

class AsyncTaskException extends Exception
{
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}