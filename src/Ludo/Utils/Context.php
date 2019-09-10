<?php
namespace Ludo\Utils;

use Swoole\Coroutine;

class Context
{
    /**
     * @var array $context
     */
    private static $context = [];

    /**
     * @var Context $instance context instance
     */
    private static $instance;

    /**
     * Get context instance
     *
     * @return Context
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Context();
        }
        return self::$instance;
    }

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
            return self::$context[$id] ?? $default;
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
            self::$context[$id] = $value;
        }
    }
}