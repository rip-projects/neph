<?php namespace Neph\Core\DB\ORM;

use \Neph\Core\Loader;
use \Neph\Core\DB;

class ModelFactory {
    var $key = 'id';

    function __construct($name, $key = 'id') {
        $this->name = $name;
        $this->key = $key;
        $this->model = Loader::model($name);
    }

    function _columns() {
        return DB::columns($this->name);
    }

    function query() {
        return DB::table($this->name);
    }

    function _all() {
        return $this->query()->get();
    }

    function _find($arg) {
        return $this->query()->find($arg);
    }

    function _create($data) {
        $id = $this->query()->insert_get_id($data, $this->key);
        $d = $this->query()->find($id);
        return new Model((array)$d);
    }

    function __call($method, $args) {
        if ($this->model) {
            return call_user_func_array(array($this->model, $method), $args);
        } else {
            if (!method_exists($this, '_'.$method)) {
                throw new \Exception('Method [ModelFactory::_'. $method .'] not available');
            } else {
                return call_user_func_array(array($this, '_'.$method), $args);
            }
        }
    }
}