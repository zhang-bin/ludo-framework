<?php

namespace Ludo\Context;

use Swoole\Coroutine;


/**
 * Class Repository
 *
 * @package Ludo\Context
 */
class Repository
{
    /**
     * @var array $context context data
     */
    private array $context = [];

    /**
     * Get context data
     *
     * @param string $id data unique id
     * @param null $default default value if data is empty
     * @param null $coroutineId coroutine id
     * @return mixed
     */
    public function get(string $id, $default = null, $coroutineId = null): mixed
    {
        if (inCoroutine()) {
            if (is_null($coroutineId)) {
                return Coroutine::getContext($coroutineId)[$id] ?? $default;
            } else {
                return Coroutine::getContext()[$id] ?? $default;
            }
        } else {
            return $this->context[$id] ?? $default;
        }
    }

    /**
     * Set context data
     *
     * @param string $id coroutine id
     * @param mixed $value need to saved context data
     */
    public function set(string $id, mixed $value): void
    {
        if (inCoroutine()) {
            Coroutine::getContext()[$id] = $value;
        } else {
            $this->context[$id] = $value;
        }
    }
}