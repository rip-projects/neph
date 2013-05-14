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
				$c1['type'] = $this->get_type($col);

                $t = explode('(', $col->type);
                $c1['dbtype'] = $t[0];

                if ($c1['type'] == 'integer' && $auto_increment = preg_match('/auto_increment/', $col->extra)) {
                    $c1['increment'] = $auto_increment;
                } elseif ($col->null == 'NO') {
                    $c1['filter'][] = 'required';
                    if (!in_array($c1['type'], array('string', 'text', 'password'))) {
                        $c1['filter'][] = $c1['type'];
                    }
                }


                if (!empty($t[1]) && in_array($c1['type'], array('string', 'text', 'password'))) {
                    $c1['length'] = substr($t[1], 0, count($t[1]) - 2);
                    $c1['filter'][] = 'max:'.$c1['length'];
                }

                if ($col->key == 'UNI') {
                    $c1['unique'] = true;
                    $c1['filter'][] = 'unique:'.$table;
                }

                if (isset($c1['filter'])) {
                    $c1['filter'] = implode('|', $c1['filter']);
                }

				$c[$col->field] = $c1;
			}
			$columns[$table] = $c;
		}
		return $columns[$table];
	}

	function get_type($col) {
        if ($col->field == 'password') return 'password';
        if (stripos($col->type, 'int') !== FALSE) {
            return 'integer';
        } elseif (stripos($col->type, 'double') !== FALSE) {
            return 'decimal';
        } elseif (stripos($col->type, 'varchar') !== FALSE) {
            return 'string';
        } elseif (stripos($col->type, 'datetime') !== FALSE) {
            return 'datetime';
        } elseif (stripos($col->type, 'text') !== FALSE) {
            return 'text';
        }
        return 'unknown';
    }

}