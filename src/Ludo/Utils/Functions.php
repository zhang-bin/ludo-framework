<?php

use Ludo\Utils\Parallel;

/**
 * Parallel execute batch tasks
 *
 * @param array $callbacks
 * @return array
 */
function parallel(array $callbacks)
{
    $parallel = new Parallel();
    foreach ($callbacks as $key => $callback) {
        $parallel->add($callback, $key);
    }
    return $parallel->wait();
}