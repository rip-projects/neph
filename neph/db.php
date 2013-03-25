<?php namespace Neph;

use Neph\DB\Connection;

class DB {

	static public $connections = array();

	static public $registrar = array();

	static function connection($connection = '') {
		if (!$connection) {
			$connection = Config::get('db/default');
		}

		if ( ! isset(static::$connections[$connection])) {
			$config = Config::get("db/connections/{$connection}");
			if (is_null($config)) {
				throw new \Exception("Database connection is not defined for [$connection].");
			}

			static::$connections[$connection] = new Connection(static::connect($config), $config);
		}

		return static::$connections[$connection];
	}

	/**
	 * Get a PDO database connection for a given database configuration.
	 *
	 * @param  array  $config
	 * @return PDO
	 */
	protected static function connect($config) {
		return static::connector($config['driver'])->connect($config);
	}

	/**
	 * Create a new database connector instance.
	 *
	 * @param  string     $driver
	 * @return Database\Connectors\Connector
	 */
	protected static function connector($driver) {
		if (isset(static::$registrar[$driver])) {
			$resolver = static::$registrar[$driver]['connector'];

			return $resolver();
		}

		$driver_class = Config::get('db/drivers/'.$driver, '\\Neph\\DB\\'.$driver).'\\Connector';
		return new $driver_class();
	}

	static function table($table, $connection = '') {
		return static::connection($connection)->table($table);
	}
}