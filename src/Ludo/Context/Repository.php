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
     * @var array $context
     */
    private $context = [];

    /**
     * Get context data
     *
     * @param string $id data unique id
     * @param null $default default value if data is empty
     * @param null $coroutineId coroutine id
     * @return mixed|null
     */
    public function get(string $id, $default = null, $coroutineId = null)
    {
        if (Coroutine::getCid() > 0) {
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
     * @param string $id
     * @param $value
     */
    public function set(string $id, $value)
    {
        if (Coroutine::getCid() > 0) {
            Coroutine::getContext()[$id] = $value;
        } else {
            $this->context[$id] = $value;
        }
    }
}