<?php namespace Neph\Core\DB\ORM;

use \Neph\Core\Controller;
use \Neph\Core\DB;

class Collection {
    const COLUMN_KEYS = 1;
    const COLUMN_VALUES = 2;

    public static $model_base_class = '\\Neph\\Core\\DB\\ORM\\Model';

    protected $name = '';
    protected $alias = '';
    protected $class = '';
    protected $connection;

    protected $proto;
    protected $columns;

    protected $table;

    public $passthru = array(
        'lists', 'only', 'insert', 'insert_get_id', 'update', 'increment',
        'delete', 'decrement', 'count', 'min', 'max', 'avg', 'sum',
    );

    function __construct($name) {
        $this->name = $name;
        $this->class = Controller::get_class($this->name, '', 'model');

        if (empty($this->class)) {
            $this->class = static::$model_base_class;
        }

        $this->proto = $this->prototype();
        $this->alias = ($this->proto->alias) ? $this->proto->alias : $this->name;
    }

    public function connection() {
        return DB::connection($this->proto->connection);
    }

    protected function hydrate($result) {
        $class = $this->class;
        $models = array();
        foreach ((array) $result as $row) {
            $row = (array) $row;
            $new = $this->prototype($row, array('exists' => true));

            $models[] = $new;
        }

        return $models;
    }

    protected function table($new = false) {
        if (empty($this->table) || $new) {
            $this->table = $this->connection()->table($this->name);
        }
        return $this->table;
    }

    public function inflate($key, $value) {
        return $this->connection()->grammar()->inflate($value, $this->column($key.'.type'));
    }

    public function find($id, $columns = array('*'), $show_all = false) {
        $this->table(true)->where($this->proto->key(), '=', $id)->take(1);
        $result = $this->get($columns, $show_all);
        if (!empty($result)) return $result[0];
    }

    public function all($columns = array('*'), $show_all = false) {
        $this->table(true);
        return $this->get($columns, $show_all);
    }

    public function get($columns = array('*'), $show_all = false) {
        $query = $this->table();
        if (!$show_all) {
            $query->where($this->proto->key('status'), '>', 0);
        }
        $result = $query->get($columns);
        $this->table = null;
        return $this->hydrate($result);
    }

    public function root($columns = array('*'), $show_all = false) {
        $this->table()->where($this->proto->key('parent'), '=', 0);
        return $this->get($columns, $show_all);
    }

    public function prototype($attributes = '', $options = array()) {
        // FIXME still confuse what did I want to achieve with this before, LoL
        if ($this->class == static::$model_base_class && empty($options['alias'])) {
            $options['name'] = $this->name;
        }
        return new $this->class($attributes, $options);
    }

    public function hidden() {
        return $this->proto->hidden();
    }

    public function key($key_name = '') {
        return $this->proto->key($key_name);
    }

    public function column($key = '', $type = 3) {
        if (!isset($this->columns)) {
            $this->columns = ($this->proto->columns)
                ? $this->proto->columns
                : $this->connection()->columns($this->alias);
            $this->proto->prepare_columns($this->columns);

            $name = $this->key('name');
            if (!$name) {
                $column_keys = array_keys($this->columns);
                $name = (isset($column_keys[1])) ? $column_keys[1] : $column_keys[0];
            }

            if (isset($this->columns[$this->key('parent')])) {
                $this->columns[$this->key('parent')]['source'] = 'model:'.$this->name.':'.$this->key().':'.$name;
            }
        }

        if (!empty($key)) {
            return get($this->columns, $key);
        } elseif ($type == 3) {
            return $this->columns;
        } elseif ($type == Collection::COLUMN_VALUES) {
            return array_values($this->columns);
        } elseif ($type == Collection::COLUMN_KEYS) {
            return array_keys($this->columns);
        }
    }

    function __call($method, $parameters) {
        $result = call_user_func_array(array($this->table(), $method), $parameters);
        if (in_array($method, $this->passthru)) {
            return $result;
        }
        return $this;
    }
}