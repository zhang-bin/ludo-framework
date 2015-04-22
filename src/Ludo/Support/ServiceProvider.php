<?php
namespace Ludo\Support;

use Ludo\Database\DatabaseManager;
use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Config\Config;
use Ludo\View\View;
use Ludo\Task\TaskQueueServer;
use Monolog\Logger as MonoLog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;

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
	 * @var MonoLog
	 */
	private $log = null;

    private static $instance = null;

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
			$this->dbManager = new DatabaseManager($this->config, new ConnectionFactory());
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
     * @param string $name
     * @param int $level
     * @param string $filename
     * @return MonoLog
     */
	public function getLogHandler($name = 'log', $level = MonoLog::DEBUG, $filename = null)
    {
        is_null($filename) && $filename = SITE_ROOT.'/log/access.log';
		if ($this->log == null) {
			$this->log = new MonoLog($name);
            $this->log->pushHandler(new StreamHandler($filename, $level));
            $this->log->pushHandler(new ChromePHPHandler($level));
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
}
