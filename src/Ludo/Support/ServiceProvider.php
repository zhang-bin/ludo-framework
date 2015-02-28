<?php
namespace Ludo\Support;

use Ludo\Database\DatabaseManager;
use Ludo\Database\Connectors\ConnectionFactory;
use Ludo\Config\Config;
use Ludo\View\View;
use Ludo\Foundation\Lang;
/**
 * The kernel of the framework which holds all available resource
 */
class ServiceProvider {
	private $_db = array();
	private $_config = array();

	/**
	 * @var \Ludo\View\View
	 */
	private $_tpl = null;

	/**
	 * @var \Ludo\Database\DatabaseManager
	 */
	private $_dbManager = null;

	static private $_instance = null;

	private function __construct() {
		$this->_config = Config::getConfig();
	}

	/**
	 * get unique instance of kernel
	 * @return ServiceProvider
	 */
	static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new ServiceProvider();
		}
		return self::$_instance;
	}

	/**
	 * get DB Handler
	 *
	 * @param string $name db instance in database config
	 * @return \Ludo\Database\Connection an instance of DBHandler
	 */
	public function getDBHandler($name = null) {
		$this->getDBManagerHandler();
		$name = $name ?: $this->_dbManager->getDefaultConnection();
		if (empty($this->_db[$name])) {
			$this->_db[$name] = $this->_dbManager->connection($name);
		}
		return $this->_db[$name];
	}

	/**
	 * @return \Ludo\Database\DatabaseManager
	 */
	public function getDBManagerHandler() {
		if (empty($this->_dbManager)) {
			$this->_dbManager = new DatabaseManager($this->_config, new ConnectionFactory());
		}
		return $this->_dbManager;
	}

	/**
	 *
	 * @return \Ludo\View\View
	 */
	public function getTplHandler() {
		if ($this->_tpl == null) {
			$this->_tpl = new View();
		}
		return $this->_tpl;
	}
}