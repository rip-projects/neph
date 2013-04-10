<?php namespace Neph\Core;

class Cookie {

    /**
     * How long is forever (in minutes)?
     *
     * @var int
     */
    const forever = 2628000;

    /**
     * The cookies that have been set.
     *
     * @var array
     */
    public static $jar = array();

    /**
     * Determine if a cookie exists.
     *
     * @param  string  $name
     * @return bool
     */
    public static function has($name) {
        return ! is_null(static::get($name));
    }

    /**
     * Get the value of a cookie.
     *
     * <code>
     *      // Get the value of the "favorite" cookie
     *      $favorite = Cookie::get('favorite');
     *
     *      // Get the value of a cookie or return a default value
     *      $favorite = Cookie::get('framework', 'Laravel');
     * </code>
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return string
     */
    public static function get($name, $default = null) {
        $d = static::raw_get($name, $default);
        if (isset($d)) return static::parse($d);

        return value($default);
    }

    public static function raw_get($name, $default = null) {
        if (isset(static::$jar[$name])) return static::$jar[$name]['value'];

        if (!is_null($value = Request::cookie($name))) {
            return $value;
        }
    }

    /**
     * Set the value of a cookie.
     *
     * <code>
     *      // Set the value of the "favorite" cookie
     *      Cookie::put('favorite', 'Laravel');
     *
     *      // Set the value of the "favorite" cookie for twenty minutes
     *      Cookie::put('favorite', 'Laravel', 20);
     * </code>
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $expiration
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @return void
     */
    public static function put($name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false) {
        $value = static::hash($value).'+'.$value;
        static::raw_put($name, $value, $expiration, $path, $domain, $secure);
    }

    public static function raw_put($name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false) {
        if ($expiration !== 0) {
            $expiration = time() + $expiration;
        }

        if ($domain === 'localhost') {
            $domain = false;
        }

        // If the secure option is set to true, yet the request is not over HTTPS
        // we'll throw an exception to let the developer know that they are
        // attempting to send a secure cookie over the insecure HTTP.
        if ($secure and ! Request::secure()) {
            throw new \Exception("Attempting to set secure cookie over HTTP.");
        }

        static::$jar[$name] = compact('name', 'value', 'expiration', 'path', 'domain', 'secure');
        static::$jar[$name]['http_only'] = false;
    }

    /**
     * Set a "permanent" cookie. The cookie will last for one year.
     *
     * <code>
     *      // Set a cookie that should last one year
     *      Cookie::forever('favorite', 'Blue');
     * </code>
     *
     * @param  string  $name
     * @param  string  $value
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @return bool
     */
    public static function forever($name, $value, $path = '/', $domain = null, $secure = false)
    {
        return static::put($name, $value, static::forever, $path, $domain, $secure);
    }

    /**
     * Delete a cookie.
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @return bool
     */
    public static function forget($name, $path = '/', $domain = null, $secure = false)
    {
        return static::put($name, null, -2000, $path, $domain, $secure);
    }

    /**
     * Hash the given cookie value.
     *
     * @param  string  $value
     * @return string
     */
    public static function hash($value)
    {
        return hash_hmac('sha1', $value, Config::get('config.key'));
    }

    /**
     * Parse a hash fingerprinted cookie value.
     *
     * @param  string  $value
     * @return string
     */
    protected static function parse($value)
    {
        $segments = explode('+', $value);

        // First we will make sure the cookie actually has enough segments to even
        // be valid as being set by the application. If it does not we will go
        // ahead and throw exceptions now since there the cookie is invalid.
        if ( ! (count($segments) >= 2))
        {
            return null;
        }

        $value = implode('+', array_slice($segments, 1));

        // Now we will check if the SHA-1 hash present in the first segment matches
        // the ShA-1 hash of the rest of the cookie value, since the hash should
        // have been set when the cookie was first created by the application.
        if ($segments[0] == static::hash($value))
        {
            return $value;
        }

        return null;
    }

}
