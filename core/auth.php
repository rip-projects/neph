<?php namespace Neph\Core;

use Closure;
use \Neph\Core\Router\Route;

class Auth {

    /**
     * The currently active authentication drivers.
     *
     * @var array
     */
    public static $drivers = array();

    /**
     * The third-party driver registrar.
     *
     * @var array
     */
    public static $registrar = array();

    public static function load($auto = false) {
        if ($auto) {

            static::driver()->load();
        }
    }

    public static function loaded() {
        return !empty(static::$drivers);
    }

    /**
     * Get an authentication driver instance.
     *
     * @param  string  $driver
     * @return Driver
     */
    public static function driver($driver = null) {
        if (is_null($driver)) $driver = Config::get('auth.default');

        if ( ! isset(static::$drivers[$driver])) {
            static::$drivers[$driver] = static::factory($driver);
        }

        return static::$drivers[$driver];
    }

    /**
     * Create a new authentication driver instance.
     *
     * @param  string  $driver
     * @return Driver
     */
    protected static function factory($driver) {
        if (isset(static::$registrar[$driver])) {
            $resolver = static::$registrar[$driver];

            return $resolver();
        }

        switch ($driver) {
            case 'database':
                return new \Neph\Core\Auth\Drivers\Database(Config::get('auth.database'));

            default:
                throw new \Exception("Auth driver {$driver} is not supported.");
        }
    }

    /**
     * Register a third-party authentication driver.
     *
     * @param  string   $driver
     * @param  Closure  $resolver
     * @return void
     */
    public static function extend($driver, Closure $resolver)
    {
        static::$registrar[$driver] = $resolver;
    }

    /**
     * Magic Method for calling the methods on the default cache driver.
     *
     * <code>
     *      // Call the "user" method on the default auth driver
     *      $user = Auth::user();
     *
     *      // Call the "check" method on the default auth driver
     *      Auth::check();
     * </code>
     */
    public static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::driver(), $method), $parameters);
    }

}