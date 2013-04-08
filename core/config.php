<?php namespace Neph\Core;

class Config {
	static public $instance;

	static function instance() {
		if (empty(static::$instanc)) {
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

		$env = $this->get('config.environment', 'development');
		$this->path(Neph::path('site').Neph::site().'/config/'.$env.'/');

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

		if (!$this->get('config.key')) {
			$this->set('config.key', 'password');
		}

		if (!$this->get('config.encoding')) {
			$this->set('config.encoding', 'utf8');
		}
	}

	function path($path) {
		array_unshift($this->paths, $path);
	}

	function set($key, $value) {
		array_set($this->cache, $key, $value);
	}

	function get($key, $def = '') {
		$segments = explode('.', $key);

		$result = array_get($this->cache, $key);

		if (!isset($result)) {

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
			}

			$result = array_get($this->cache, $key);
		}

		if (!isset($result)) $result = $def;

		return $result;
	}
};
