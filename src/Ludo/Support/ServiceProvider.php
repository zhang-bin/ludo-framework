<?php

namespace Ludo\Support;

use Ludo\Database\DatabaseManager;
use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Redis\BaseRedis;
use Ludo\Redis\RedisManager;
use Ludo\Support\Facades\Config;
use Ludo\View\View;
use Ludo\Database\Connection;
use Closure;
use Swoole\Coroutine;
use RedisException;


/**
 * The kernel of the framework which holds all available resource
 */
class ServiceProvider
{
    /**
     * @var Connection[] $db connections
     */
    private array $db = [];

    /**
     * @var ?View $tpl view object
     */
    private ?View $tpl = null;

    /**
     * @var ?DatabaseManager $dbManager database manager object
     */
    private ?DatabaseManager $dbManager = null;

    /**
     * @var ?RedisManager $redisManager redis manager object
     */
    private ?RedisManager $redisManager = null;

    /**
     * @var BaseRedis[] $redis redis object
     */
    private array $redis = [];

    /**
     * @var ServiceProvider[] $instance self instances
     */
    private static array $instance = [];

    /**
     * @var array $bindings register class relationship
     */
    private array $bindings = [];

    /**
     * Get unique instance of kernel
     *
     * @param ?int $cid coroutine id
     * @return ServiceProvider
     */
    public static function getInstance(?int $cid = null): ServiceProvider
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
    public static function getMainInstance(): ServiceProvider
    {
        return self::getInstance(-1);
    }

    /**
     * Get DB Handler
     *
     * @param ?string $name db instance in database config
     * @return Connection an instance of DBHandler
     */
    public function getDBHandler(?string $name = null): Connection
    {
        $this->getDBManagerHandler();
        $name = $name ?: $this->dbManager->getDefaultConnection();
        if (empty($this->db[$name])) {
            $this->db[$name] = $this->dbManager->connection($name);
        }
        return $this->db[$name];
    }

    /**
     * Delete all DB connections
     *
     * @param ?string $name db instance
     */
    public function delDBHandler(?string $name = null): void
    {
        $this->db[$name] = null;
    }

    /**
     * Get DB Manager Handler
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
     * @param ?string $name redis name
     * @return BaseRedis
     * @throws RedisException
     */
    public function getRedisHandler(?string $name = null): BaseRedis
    {
        $this->getRedisManagerHandler();
        $name = $name ?: $this->redisManager->getDefaultConnection();
        if (empty($this->redis[$name])) {
            $this->redis[$name] = $this->redisManager->connection($name);
        }
        return $this->redis[$name];
    }

    /**
     * Delete redis handler
     *
     * @param ?string $name redis name
     */
    public function delRedisHandler(?string $name = null): void
    {
        $this->redis[$name] = null;
    }

    /**
     * Get Redis Manager Handler
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
     * Get View Handler
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
     * Register class callback
     *
     * @param string $abstract class name
     * @param Closure $concrete callback
     */
    public function register(string $abstract, Closure $concrete): void
    {
        if (isset($this->bindings[$abstract])) {//已经注册了
            return;
        }

        $this->bindings[$abstract] = call_user_func($concrete);
    }

    /**
     * Get registered class callback
     *
     * @param string $abstract class name
     * @return mixed
     */
    public function getRegisteredAbstract(string $abstract): mixed
    {
        return $this->bindings[$abstract];
    }
}
