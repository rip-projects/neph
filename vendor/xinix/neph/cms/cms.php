<?php namespace Xinix\Neph\CMS;

class CMS {
    static function find_controller_by_module_name() {
        return new CMS_Controller;
    }

    function __call($method, $args) {

    }
}