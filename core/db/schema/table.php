<?php
namespace Neph\Core\DB\Schema;

class Table {
    public $name;
    public $connection;
    public $engine;
    public $columns = array();
    public $commands = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function create() {
        return $this->command(__FUNCTION__);
    }

    protected function command($type, $parameters = array()) {
        $parameters = array_merge(compact('type'), $parameters);
        return $this->commands[] = $parameters;
    }

    public function increments($name) {
        return $this->integer($name, true);
    }

    public function integer($name, $increment = false) {
        return $this->column(__FUNCTION__, compact('name', 'increment'));
    }

    public function string($name, $length = 200) {
        return $this->column(__FUNCTION__, compact('name', 'length'));
    }

    protected function column($type, $parameters = array()) {
        $parameters = array_merge(compact('type'), $parameters);
        return $this->columns[] = $parameters;
    }

    /**
     * Determine if the schema table has a creation command.
     *
     * @return bool
     */
    public function creating()
    {
        return ! is_null(array_first($this->commands, function($key, $value) {
            return get($value, 'type') == 'create';
        }));
    }
}