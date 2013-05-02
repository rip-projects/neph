<?php namespace Neph\Core\DB\ORM;

class Manager {
    protected $collections = array();
    function collection($name) {
        if (empty($this->collections[$name])) {
            $this->collections[$name] = new Collection($name);
        }

        return $this->collections[$name];
    }
}
