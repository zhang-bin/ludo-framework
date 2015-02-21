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
	private $_db = null;
	private $_config = array();
	private $_tpl = null;
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
	public function getDBHandler($name = '') {
		if (empty($this->_db)) {
			$this->_dbManager = new DatabaseManager($this->_config, new ConnectionFactory());
			$this->_db = $this->_dbManager->connection($name);
		}
		return $this->_db;
	}

	/**
	 * @return \Ludo\Database\DatabaseManager
	 */
	public function getDBManagerHandler() {
		if (empty($this->_dbManager)) {
			$this->getDBHandler();
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