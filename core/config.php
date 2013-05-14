<?php namespace Neph\Core;

class Config {
	static public $instance;

	var $paths = array();
	var $cache = array();

	static function instance() {
		if (!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	function __call($method, $parameters) {
		return call_user_func_array(array($this, '_'.$method), $parameters);
	}

	static function __callStatic($method, $parameters) {
		return call_user_func_array(array(static::instance(), $method), $parameters);
	}

	function __construct() {
		$this->path(Neph::path('sys').'config/');
		$this->path(Neph::path('site').Neph::site().'/config/');

		$env = $this->get('config.environment', 'development');
		$this->path(Neph::path('site').Neph::site().'/config/'.$env.'/');

		if (!is_cli()) {
			if (!$this->get('config.url')) {
				if ($this->get('config.index')) {
					$sep = $this->get('config.index').((isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '');
					$req = explode($sep, $_SERVER['REQUEST_URI'], 2);
					$domain = (empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['HTTP_HOST'].($_SERVER['SERVER_PORT'] == 80 ? '' : (':'.$_SERVER['SERVER_PORT']) );
					$url = $domain.$req[0];
					$this->set('config.url', $url);
					$this->set('config.base_path', $req[0]);
				}
			}
		}

		if (!$this->get('config.key')) {
			$this->set('config.key', 'password');
		}

		if (!$this->get('config.encoding')) {
			$this->set('config.encoding', 'utf8');
		}
	}

	function _path($path) {
		$this->paths[$path] = $path;
	}

	function _set($key, $value) {
		array_set($this->cache, $key, $value);
	}

	function _get($key, $def = '') {

		$result = array_get($this->cache, $key);
		if (isset($result)) return $result;

		$segments = explode('.', $key);
		if (!isset($this->cache[$segments[0]])) {
			$this->cache[$segments[0]] = array();

			$config = array();
			foreach($this->paths as $path) {
				if (is_readable($f = $path.$segments[0].'.php')) {
					$c = include($f);
					if (is_array($c)) {
						$config = array_merge_recursive_distinct($config, $c);
					}
				}
			}

			if (!empty($config)) {
				$this->cache[$segments[0]] = array_merge_recursive_distinct($this->cache[$segments[0]], $config);
			}
		}

		return array_get($this->cache, $key, $def);
	}
}