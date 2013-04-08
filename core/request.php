<?php namespace Neph\Core;

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
	var $accept = '';

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

	function cookie($key, $value = '__GET__') {
		if ($value === '__GET__') {
			return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : NULL;
		}
	}

	function is_rest() {
		if ($this->uri->extension == 'json') return true;
		$rest_content_types = array('application/json');
		if (!empty($_SERVER['CONTENT_TYPE']) && in_array($_SERVER['CONTENT_TYPE'], $rest_content_types)) return true;
		$accept = $this->accept();
		if (in_array($accept[0]['mime'], $rest_content_types)) return true;
		return false;
	}

	function accept() {
		if (empty($this->accept)) {
			$accepting = explode(',', $_SERVER['HTTP_ACCEPT']);
			foreach ($accepting as &$value) {
				$exploded = explode(';', $value);
				$value = array(
					'mime' => trim($exploded[0]),
					'priority' => (empty($exploded[1]) ? 1 : doubleval($exploded[1])),
				);
			}
			$this->accept = $accepting;
		}
		return $this->accept;
	}

	function data() {
		switch ($_SERVER['CONTENT_TYPE']) {
			case 'application/json':
				return json_decode(file_get_contents('php://input'), true);
			default:
				return $_POST;
		}
	}
}