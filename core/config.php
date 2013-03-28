<?php namespace Neph\Core;

class Config {
	static private $instance;

	static function instance() {
		if (!static::$instance) {
			static::$instance = new ConfigImpl();
		}
		return static::$instance;
	}

	static function __callStatic($method, $parameters) {
		return call_user_func_array(array(static::instance(), $method), $parameters);
	}
}

class ConfigImpl {
	var $paths = array();
	var $cache = array();

	function __construct() {
		$this->path(Neph::path('sys').'config/');
		$this->path(Neph::path('site').Neph::site().'/config/');

		$env = $this->get('config/environment', 'development');
		$this->path(Neph::path('site').Neph::site().'/config/'.$env.'/');
	}

	function path($path) {
		array_unshift($this->paths, $path);
	}

	function set($key, $value) {
		$segments = explode('/', $key);
		$config = &$this->cache;
		foreach ($segments as $k) {
			if (empty($config[$k])) {
				$config[$k] = array();
			}
			$config = &$config[$k];
		}
		$config = $value;
	}

	function get($key, $def = '') {
		$segments = explode('/', $key);

		$found = true;
		$config = $this->cache;
		foreach ($segments as $value) {
			if (!isset($config[$value])) {
				$found = false;
				break;
			}
			$config = $config[$value];
		}

		if ($found) {
			return $config;
		}

		$config = array();
		foreach($this->paths as $path) {
			if (is_readable($path.$segments[0].'.php')) {
				$c = include($path.$segments[0].'.php');
				if (is_array($c)) {
					$config = $config + $c;
				}
			}
		}

		if (!empty($config)) {
			$this->cache[$segments[0]] = $config;

			$found = true;
			$config = $this->cache;
			foreach ($segments as $value) {
				if (!isset($config[$value])) {
					$found = false;
					break;
				}
				$config = $config[$value];
			}
			if ($found) return $config;
		}

		return $def;
	}

	function init() {
		if (!$this->get('config/url')) {
			$sep = $this->get('config/index').((isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '');
			$req = explode($sep, $_SERVER['REQUEST_URI'], 2);
			$url = (empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['HTTP_HOST'].($_SERVER['SERVER_PORT'] == 80 ? '' : (':'.$_SERVER['SERVER_PORT']) ).$req[0];
			$this->set('config/url', $url);
		}
	}
};

Config::init();