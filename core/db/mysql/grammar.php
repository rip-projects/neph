<?php namespace Neph\Core\DB\MySQL;

use \Neph\Core\DB\Schema\Table;
use \Neph\Core\DB\Query;

class Grammar extends \Neph\Core\DB\Query\Grammar {

	/**
	 * The keyword identifier for the database system.
	 *
	 * @var string
	 */
	protected $wrapper = '`%s`';

    public function truncate(Query $query) {
        $table = $this->wrap_table($query->from);

        return trim("TRUNCATE {$table}");
    }

    public function create(Table $table, $command) {
        // $columns = implode(', ', $this->table_columns($table));
        $columns = array();
        foreach ($table->columns as $key => $column) {
            $method = 'column_'.$column['type'];
            $columns[] = $c = $this->$method($column);

        }
        $columns = implode(','.PHP_EOL, $columns);

        // First we will generate the base table creation statement. Other than auto
        // incrementing keys, no indexes will be created during the first creation
        // of the table as they're added in separate commands.
        $sql = 'CREATE TABLE '.$this->wrap($table->name).' ('.$columns.')';

        if ( ! is_null($table->engine))
        {
            $sql .= ' ENGINE = '.$table->engine;
        }

        return $sql;
    }

    function column_integer($column) {
        $column['length'] = get($column, 'length', 11);
        $col = $this->wrap($column['name']).' INT('.$column['length'].')';
        if (!empty($column['increment'])) {
            $col .= ' AUTO_INCREMENT PRIMARY KEY';
        }
        return $col;
    }

    function column_string($column) {
        $col = $this->wrap($column['name']).' VARCHAR('.$column['length'].')';
        return $col;
    }

	function columns($q) {
		return 'SHOW COLUMNS FROM '.$this->wrap($q);
	}

    function check($q) {
        return "SHOW TABLES LIKE '$q'";
    }
}