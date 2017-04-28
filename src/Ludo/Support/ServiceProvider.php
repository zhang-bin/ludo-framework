<?php
namespace Ludo\Support;

use Ludo\Database\DatabaseManager;
use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Config\Config;
use Ludo\View\View;
use Ludo\Task\TaskQueueServer;
use Ludo\Log\Logger;

/**
 * The kernel of the framework which holds all available resource
 */
class ServiceProvider
{
	private $db = array();
	private $config = array();

	/**
	 * @var \Ludo\View\View
	 */
	private $tpl = null;

	/**
	 * @var \Ludo\Database\DatabaseManager
	 */
	private $dbManager = null;

	/**
	 * @var Logger
	 */
	private $log = null;

	private static $instance = null;

	private $bindings = [];

	private function __construct()
    {
		$this->config = Config::getConfig();
	}

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
	 * @return \Ludo\Database\Connection an instance of DBHandler
	 */
	public function getDBHandler($name = null)
    {
		$this->getDBManagerHandler();
		$name = $name ?: $this->dbManager->getDefaultConnection();
		if (empty($this->db[$name])) {
			$this->db[$name] = $this->dbManager->connection($name);
		}
		return $this->db[$name];
	}

	/**
	 * get DB Manager Handler
	 *
	 * @return \Ludo\Database\DatabaseManager
	 */
	public function getDBManagerHandler()
    {
		if (empty($this->dbManager)) {
			$this->dbManager = new DatabaseManager($this->config['database'], new ConnectionFactory());
		}
		return $this->dbManager;
	}

	/**
	 * get View Handler
	 *
	 * @return \Ludo\View\View
	 */
	public function getTplHandler()
    {
		if ($this->tpl == null) {
			$this->tpl = new View();
		}
		return $this->tpl;
	}

    /**
     * get Log Handler
     *
     * @return Logger
     */
	public function getLogHandler()
    {
        if ($this->log == null) {
            $this->log = new Logger();
        }
        return $this->log;
	}

    /**
     * 异步任务server
     *
     * @param $cmd
     */
    public function taskQueueServer($cmd)
    {
        $server = new TaskQueueServer();
        $client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_SYNC);
        switch ($cmd) {
            case 'start':
                $server->run();
                break;
            case 'stop':
                $client->connect(Config::get('server.task_queue.host'), Config::get('server.task_queue.port'));
                $client->send('stop');
                break;
            case 'reload':
                $client->connect(Config::get('server.task_queue.host'), Config::get('server.task_queue.port'));
                $client->send('reload');
                break;
            default:
                break;
        }
    }

    public function register($abstract, $concrete) {
        if (isset($this->bindings[$abstract])) return;//已经注册了

        $this->bindings[$abstract] = call_user_func($concrete);
    }

    public function getRegisteredAbstract($abstract)
    {
        return $this->bindings[$abstract];
    }

}
