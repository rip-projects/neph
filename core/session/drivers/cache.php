<?php namespace Neph\Core\Session\Drivers;

use \Neph\Core\Cache as C;

class Cache extends Driver {

    private $driver;
    private $config;

    function __construct($config) {
        $this->config = $config;
        $this->driver = C::driver($config['cache_driver']);
    }

    function load($id) {
        return $this->driver->get($id);
    }

    function save($session, $config, $exists) {
        $this->driver->put($session['id'], $session, $config['lifetime']);
    }

    function delete($id) {
        $this->driver->forget($id);
    }
}
