<?php

namespace Ludo\Exception;

use Exception;

class ApplicationException extends Exception
{
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}