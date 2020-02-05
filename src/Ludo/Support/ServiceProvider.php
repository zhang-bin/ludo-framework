<?php

namespace Ludo\Support;

use Ludo\Database\DatabaseManager;
use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Redis\BaseRedis;
use Ludo\Redis\RedisManager;
use Ludo\Support\Facades\Config;
use Ludo\Support\Facades\Log;
use Ludo\View\View;
use Ludo\Log\Logger;
use Ludo\Database\Connection;
use Closure;
use Swoole\Coroutine;

/**
 * The kernel of the framework which holds all available resource
 */
class ServiceProvider
{
    /**
     * @var array
     */
    private $db = [];

    /**
     * @var View
     */
    private $tpl = null;

    /**
     * @var DatabaseManager
     */
    private $dbManager = null;

    /**
     * @var RedisManager
     */
    private $redisManager = null;

    /**
     * @var array
     */
    private $redis = [];

    /**
     * @var Logger
     */
    private $log = null;

    private static $instance = [];

    private $bindings = [];

    /**
     * get unique instance of kernel
     *
     * @param int $cid
     * @return ServiceProvider
     */
    public static function getInstance(int $cid = null)
    {
        if (is_null($cid)) {
            $cid = Coroutine::getCid();
        }

        if (!isset(self::$instance[$cid])) {
            self::$instance[$cid] = new ServiceProvider();
        }
        return self::$instance[$cid];
    }

    /**
     * Get current main process instance
     *
     * @return ServiceProvider
     */
    public static function getMainInstance()
    {
        return self::getInstance(-1);
    }

    /**
     * get DB Handler
     *
     * @param string $name db instance in database config
     * @return Connection an instance of DBHandler
     */
    public function getDBHandler(string $name = null): Connection
    {
        $this->getDBManagerHandler();
        $name = $name ?: $this->dbManager->getDefaultConnection();
        if (empty($this->db[$name])) {
            $this->db[$name] = $this->dbManager->connection($name);
        }
        return $this->db[$name];
    }

    /**
     * delete all DB connections
     * @param string $name
     */
    public function delDBHandler(string $name = null): void
    {
        $this->db[$name] = null;
    }

    /**
     * get DB Manager Handler
     *
     * @return DatabaseManager
     */
    public function getDBManagerHandler(): DatabaseManager
    {
        if (empty($this->dbManager)) {
            $this->dbManager = new DatabaseManager(Config::get('database'), new ConnectionFactory());
        }
        return $this->dbManager;
    }

    /**
     * Get redis handler
     *
     * @param string|null $name
     * @return BaseRedis
     */
    public function getRedisHandler(string $name = null): BaseRedis
    {
        $this->getRedisManagerHandler();
        $name = $name ?: $this->redisManager->getDefaultConnection();
        if (empty($this->redis[$name])) {
            $this->redis[$name] = $this->redisManager->connection($name);
        }
        return $this->redis[$name];
    }

    /**
     * delete redis handler
     *
     * @param string|null $name
     */
    public function delRedisHandler(string $name = null)
    {
        $this->redis[$name] = null;
    }

    /**
     * get Redis Manager Handler
     *
     * @return RedisManager
     */
    public function getRedisManagerHandler(): RedisManager
    {
        if (empty($this->redisManager)) {
            $this->redisManager = new RedisManager(Config::get('redis'));
        }
        return $this->redisManager;
    }

    /**
     * get View Handler
     *
     * @return View
     */
    public function getTplHandler(): View
    {
        if ($this->tpl == null) {
            $this->tpl = new View();
        }
        return $this->tpl;
    }

    /**
     * get Log Handler
     *
     * @return Log
     */
    public function getLogHandler(): Log
    {
        if ($this->log == null) {
            $this->log = new Log();
        }
        return $this->log;
    }

    public function register(string $abstract, Closure $concrete): void
    {
        if (isset($this->bindings[$abstract])) {//已经注册了
            return;
        }

        $this->bindings[$abstract] = call_user_func($concrete);
    }

    public function getRegisteredAbstract(string $abstract)
    {
        return $this->bindings[$abstract];
    }

}
