<?php
namespace Ludo\Database\Connectors;

use PDO;
use Ludo\Database\MySqlConnection;

class ConnectionFactory {
	/**
	 * Establish a PDO connection based on the configuration.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return \Ludo\Database\Connection
	 */
	public function make(array $config, $name = null) {
		$config = $this->parseConfig($config, $name);
		if (isset($config['read'])) {
			return $this->createReadWriteConnection($config);
		} else {
			return $this->createSingleConnection($config);
		}
	}

	/**
	 * Create a single database connection instance.
	 *
	 * @param  array  $config
	 * @return \Ludo\Database\Connection
	 */
	protected function createSingleConnection(array $config) {
		$pdo = $this->createConnector($config)->connect($config);
		return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
	}

	/**
	 * Create a single database connection instance.
	 *
	 * @param  array  $config
	 * @return \Ludo\Database\Connection
	 */
	protected function createReadWriteConnection(array $config) {
		$connection = $this->createSingleConnection($this->getWriteConfig($config));
		return $connection->setReadPdo($this->createReadPdo($config));
	}

	/**
	 * Create a new PDO instance for reading.
	 *
	 * @param  array  $config
	 * @return \PDO
	 */
	protected function createReadPdo(array $config) {
		$readConfig = $this->getReadConfig($config);
		return $this->createConnector($readConfig)->connect($readConfig);
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @return array
	 */
	protected function getReadConfig(array $config) {
		$readConfig = $this->getReadWriteConfig($config, 'read');
		return $this->mergeReadWriteConfig($config, $readConfig);
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @return array
	 */
	protected function getWriteConfig(array $config) {
		$writeConfig = $this->getReadWriteConfig($config, 'write');
		return $this->mergeReadWriteConfig($config, $writeConfig);
	}

	/**
	 * Get a read / write level configuration.
	 *
	 * @param  array  $config
	 * @param  string  $type
	 * @return array
	 */
	protected function getReadWriteConfig(array $config, $type) {
		if (isset($config[$type][0])) {
			return $config[$type][array_rand($config[$type])];
		} else {
			return $config[$type];
		}
	}

	/**
	 * Merge a configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @param  array  $merge
	 * @return array
	 */
	protected function mergeReadWriteConfig(array $config, array $merge) {
		return array_except(array_merge($config, $merge), array('read', 'write'));
	}

	/**
	 * Parse and prepare the database configuration.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return array
	 */
	protected function parseConfig(array $config, $name) {
		return array_add(array_add($config, 'prefix', ''), 'name', $name);
	}

	/**
	 * Create a connector instance based on the configuration.
	 *
	 * @param  array  $config
	 * @return \Ludo\Database\Connectors\ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createConnector(array $config) {
		if (!isset($config['driver'])) {
			throw new \InvalidArgumentException("A driver must be specified.");
		}

		switch ($config['driver']) {
			case 'mysql':
				return new MySqlConnector;
		}

		throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}]");
	}

	/**
	 * Create a new connection instance.
	 *
	 * @param  string  $driver
	 * @param  PDO     $connection
	 * @param  string  $database
	 * @param  string  $prefix
	 * @param  array   $config
	 * @return \Ludo\Database\Connection
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createConnection($driver, PDO $connection, $database, $prefix = '', array $config = array()) {
		switch ($driver) {
			case 'mysql':
				return new MySqlConnection($connection, $database, $prefix, $config);
		}

		throw new \InvalidArgumentException("Unsupported driver [$driver]");
	}

}