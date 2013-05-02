<?php namespace Neph\Core\DB\ORM;

use \Neph\Core\Controller;
use \Neph\Core\DB;

class Collection {
    protected $name = '';
    protected $class = '';
    protected $proto;

    protected $table;

    public $passthru = array(
        'lists', 'only', 'insert', 'insert_get_id', 'update', 'increment',
        'delete', 'decrement', 'count', 'min', 'max', 'avg', 'sum',
    );

    function __construct($name) {
        $this->name = $name;
        $this->class = Controller::get_class($this->name, '', 'model');

        if (empty($this->class)) {
            $this->class = '\\Neph\\Core\\DB\\ORM\\Model';
        }

        $class = $this->class;
        $this->proto = $this->prototype();
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
            $this->table = DB::connection($this->proto->connection())->table($this->name);
        }
        return $this->table;
    }

    function find($id, $columns = array('*'), $show_all = false) {
        $this->table(true)->where($this->proto->key(), '=', $id)->take(1);
        $result = $this->get($columns, $show_all);
        if (!empty($result)) return $result[0];
    }

    function all($columns = array('*'), $show_all = false) {
        $this->table(true);
        return $this->get($columns, $show_all);
    }

    function get($columns = array('*'), $show_all = false) {
        $query = $this->table();
        if (!$show_all) {
            $query->where($this->proto->key('status'), '>', 0);
        }
        $result = $query->get($columns);
        $this->table = null;
        return $this->hydrate($result);
    }

    function root($columns = array('*'), $show_all = false) {
        $this->table()->where($this->proto->key('parent'), '=', 0);
        return $this->get($columns, $show_all);
    }

    function prototype($attributes = '', $options = array()) {
        if ($this->class == '\\Neph\\Core\\DB\\ORM\\Model' && empty($options['table'])) {
            $options['name'] = $this->name;
        }
        return new $this->class($attributes, $options);
    }

    function hidden() {
        return $this->proto->hidden();
    }

    function columns() {
        return $this->proto->columns();
    }

    function __call($method, $parameters) {
        $result = call_user_func_array(array($this->table(), $method), $parameters);
        if (in_array($method, $this->passthru)) {
            return $result;
        }
        return $this;
    }
}