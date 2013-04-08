<?php namespace Neph\Core;

use Neph\Core\DB\Connection;

class DB {

	static public $connections = array();

	static function connection($connection = '') {
		if (!$connection) {
			$connection = Config::get('db.default');
		}

		if (!isset(static::$connections[$connection])) {
			$config = Config::get("db.connections.{$connection}");
			if (!isset($config)) {
				throw new \Exception("Database connection is not defined for [$connection].");
			}

			$driver_class = Config::get('db.drivers.'.$config['driver'], '\\Neph\\Core\\DB\\'.$config['driver']).'\\Connection';

			static::$connections[$connection] = new $driver_class($config);
			static::$connections[$connection]->connect();
		}

		return static::$connections[$connection];
	}

	static function __callStatic($method, $args) {
		return call_user_func_array(array(static::connection(), $method), $args);
	}
}