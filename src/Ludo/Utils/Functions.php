<?php

use Ludo\Utils\Parallel;
use Swoole\Coroutine;

/**
 * Parallel execute batch tasks
 *
 * @param array $callbacks callback
 * @return array
 */
function parallel(array $callbacks): array
{
    $parallel = new Parallel();
    foreach ($callbacks as $key => $callback) {
        $parallel->add($callback, $key);
    }
    return $parallel->wait();
}

/**
 * Decide current environment is in coroutine
 *
 * @return bool
 */
function inCoroutine(): bool
{
    return Coroutine::getCid() > 0;
}
