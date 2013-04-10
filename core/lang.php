<?php namespace Neph\Core;

class Lang {
    static $instances = array();
    static $default;
    static $cookie_key = 'LANG';
    static $cookie_expire = 60;

    public $paths = array();
    protected $key;
    protected $cache = array();

    function __construct($key = '') {
        $this->key = $key;

        $this->path(Neph::path('sys').'languages/');
        $this->path(Neph::path('site').Neph::site().'/languages/');
    }

    function path($path) {
        $this->paths[$path] = $path;
    }

    function lines($file) {
        if (!isset($this->cache[$this->key][$file])) {
            $this->cache[$this->key][$file] = array();
            foreach ($this->paths as $path) {
                if (is_readable($f = $path.$this->key.'/'.$file.'.php')) {
                    $result = include($f);
                    if (!empty($result) && is_array($result)) {
                        $this->cache[$this->key][$file] = array_merge_recursive_distinct($this->cache[$this->key][$file], $result);
                    }
                }
            }
        }

        return $this->cache[$this->key][$file];
    }

    function _line($message, $args = '') {
        if (!is_array($message)) {
            $segments = explode('.', $message);

            $message = array(
                'group' => $segments[0],
                'key' => implode('.', array_slice($segments, 1)),
            );
        }

        $message['default'] = (isset($message['default'])) ? $message['default'] : $message['group'].'.'.$message['key'];

        $line = (isset($this->cache[$this->key])) ? array_get($this->cache[$this->key], $message['group'].'.'.$message['key']) : null;
        if (!isset($line)) {
            $this->lines($message['group']);
            $line = array_get($this->cache[$this->key], $message['group'].'.'.$message['key'], $message['default']);
        }
        if (empty($args)) return $line;

        array_unshift($args, $line);
        return call_user_func_array('sprintf', $args);
    }

    function __call($method, $parameters) {
        return call_user_func_array(array($this, '_'.$method), $parameters);
    }

    static function instance($l = '') {
        $l = ($l) ?: static::$default;
        if (!isset(static::$instances[$l])) {
            static::$instances[$l] = new static($l);
        }
        return static::$instances[$l];
    }

    static function set_default($lang, $force = false) {
        if ($force || is_readable(Neph::path('site').Neph::site().'/languages/'.$lang.'/messages.php')) {
            static::$default = $lang;
            Cookie::raw_put(static::$cookie_key, $lang, static::$cookie_expire);
            return true;
        }
        return false;
    }

    static function init() {
        $ok = false;
        if ($lang = Event::until('neph.language')) $ok = static::set_default($lang);
        if (!$ok && $lang = Cookie::raw_get(static::$cookie_key)) $ok = static::set_default($lang);
        $_ls = Request::language();
        $accepted = array();
        foreach ($_ls as $v) {
            if (!$ok) $ok = static::set_default($v['lang']);
            if ($ok) break;
        }
        if (!$ok && $lang = \Config::get('config.language', 'en')) return static::set_default($lang);
        if (!$ok) return static::set_default('en', true);
    }

    static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::instance(), $method), $parameters);
    }
}