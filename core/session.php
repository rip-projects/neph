<?php namespace Neph\Core;

use Closure;

class Session {

    public static $instance;

    const csrf_token = 'csrf_token';

    public static function load() {
        static::$instance = new Session\Payload(static::factory(Config::get('session/default')));
        static::$instance->load(Cookie::get(Config::get('session/cookie', 'SESSION')));
    }

    public static function factory($name) {
        $config = Config::get('session/connections/' . $name);
        $driver_class = Config::get('db/drivers/'.$config['driver'], '\\Neph\\Core\\Session\\Drivers\\'.$config['driver']);
        return new $driver_class($config);
    }

    public static function instance() {
        if (!is_null(static::$instance)) return static::$instance;

        throw new \Exception("A driver must be set before using the session.");
    }

    public static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::instance(), $method), $parameters);
    }

}