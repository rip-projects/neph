<?php namespace Neph\Core\DB\ORM;

use \Neph\Core\DB;
use \Neph\Core\Loader;

class Model {
    static $key = 'id';
    static $table;
    static $columns = array();

    protected $attributes = array();
    protected $original = array();

    protected $exists = false;

    static function all() {
        return DB::table(static::table())->get();
    }

    static function table() {
        if (!isset(static::$table)) static::$table = strtolower(class_basename(new static()));
        return static::$table;
    }

    static function check() {
        $check = DB::check(static::table());
        throw new Exception('Unfinished yet!');
    }

    static function invoker($name) {
        return new ModelInvoker($name);
    }

    function get_key() {
        return $this->attributes[$this->key];
    }

    function save() {
        $table = DB::table(static::table());
        if ($this->exists) {
            $result = $table->where(static::$key, '=', $this->get_key())
                ->update($this->attributes);
        } else {
            $result = $table->insert_get_id($this->attributes, $this->key);
            $this->exists = (!empty($result));
        }

        $this->original = $this->attributes;

        return $result;
    }

    function columns() {
        return $this->columns;
    }

    function __set($key, $value) {
        $this->attributes[$key] = $value;
    }
}
