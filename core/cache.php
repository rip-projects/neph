<?php namespace Neph\Core;

use Closure;

class Cache {

    public static $drivers = array();

    public static function driver($driver = null) {
        if (is_null($driver)) $driver = Config::get('cache/driver');

        if ( ! isset(static::$drivers[$driver])) {
            static::$drivers[$driver] = static::factory($driver);
        }

        return static::$drivers[$driver];
    }

    protected static function factory($driver) {
        $driver_class = Config::get('db/drivers/'.$driver, '\\Neph\\Core\\Cache\\Drivers\\'.$driver);
        $d = new $driver_class(Config::get('cache/key'), Config::get('cache/connections/'.$driver, array()));
        if (empty($d)) {
            throw new \Exception("Cache driver [{$driver}] is not supported.");
        }
        return $d;
    }
    public static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::driver(), $method), $parameters);
    }

}
