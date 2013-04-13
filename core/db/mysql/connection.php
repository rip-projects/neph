<?php namespace Neph\Core\DB\MySQL;

use \PDO;

class Connection extends \Neph\Core\DB\Connection {

	public function connect() {
		extract($this->config);

		$dsn = "mysql:host={$host};dbname={$database}";

		if (isset($this->config['port'])) {
			$dsn .= ";port={$this->config['port']}";
		}

		if (isset($this->config['unix_socket'])) {
			$dsn .= ";unix_socket={$this->config['unix_socket']}";
		}

		$connection = new PDO($dsn, $username, $password, $this->options($this->config));

		if (isset($this->config['charset'])) {
			$connection->prepare("SET NAMES '{$this->config['charset']}'")->execute();
		}

		$this->connection = $connection;

		return $this;
	}

	public function check($table) {
		$check = $this->query($this->grammar()->check($table));
		return (!empty($check));
	}

	public function columns($table) {
		static $columns = array();
		if (empty($columns[$table])) {
			$columns = $this->query($this->grammar()->columns($table));
			$c = array();
			foreach ($columns as $col) {
				$c1 = array();

				$t = explode('(', $col->type);
				$c1['type'] = $t[0];
				$c1['length'] = (empty($t[1])) ? NULL : substr($t[1], 0, count($t[1]) - 2);
				$c1['auto_increment'] = preg_match('/auto_increment/', $col->extra);
				$c[$col->field] = $c1;
			}
			$columns[$table] = $c;
		}
		return $columns[$table];
	}

}