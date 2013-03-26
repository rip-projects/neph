<?php namespace Neph;

class Request {

	static $instance;

	static function instance() {
		if (!static::$instance) {
			static::$instance = new RequestImpl();
		}
		return static::$instance;
	}

	static function __callStatic($method, $parameters) {
		return call_user_func_array(array(static::instance(), $method), $parameters);
	}

}

class RequestImpl {
	var $uri;

	function __construct() {
		if (is_cli()) {
			$uri = array();
			foreach($_SERVER['argv'] as $argv) {
				$uri[] = $argv;
			}
			$uri[0] = '';
			$uri = implode('/', $uri);
		} else {
			$uri = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '/';
		}
		$this->uri = new URI($uri);
	}

	function forward($uri) {
		$this->uri = new URI($uri);
	}

	function method() {
		return (is_cli()) ? 'GET' : $_SERVER['REQUEST_METHOD'];
	}
}