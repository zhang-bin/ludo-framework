<?php

namespace Ludo\Support;

use Ludo\Database\DatabaseManager;
use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Support\Facades\Config;
use Ludo\Support\Facades\Facade;
use Ludo\Support\Facades\Log;
use Ludo\View\View;
use Ludo\Log\Logger;
use Ludo\Database\Connection;
use Closure;

/**
 * The kernel of the framework which holds all available resource
 */
class ServiceProvider
{
    private $db = array();
    /**
     * @var View
     */
    private $tpl = null;

    /**
     * @var DatabaseManager
     */
    private $dbManager = null;

    /**
     * @var Logger
     */
    private $log = null;

    private static $instance = null;

    private $bindings = [];

    /**
     * get unique instance of kernel
     *
     * @return ServiceProvider
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ServiceProvider();
        }
        return self::$instance;
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
     * $param $name
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
