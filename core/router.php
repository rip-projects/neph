<?php namespace Neph\Core;

use \Neph\Core\Router\Route;
use \Neph\Core\Router\MVCRoute;

class Router {
	static private $instance;

	private $routes = array(
		'GET' => array(),
		'POST' => array(),
	);

	static function instance() {
		if (!static::$instance) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	function register($method, $route, $fn) {
		if ($method === '*') {
			foreach ($this->routes as $key => &$routes) {
				$routes[$route] = $fn;
			}
		} else {
			$this->routes[$method][$route] = $fn;
		}
	}

	function get($key, $fn) {
		$this->register('GET', $key, $fn);
	}

	function route($request = '') {
		if ($request === '') {
			$request = Request::instance();
		} elseif (!($request instanceof Request)) {
			Request::instance()->forward($request);
			return $this->route(Request::instance());
		}

		// route to registered route if exist
		if (isset($this->routes[$request->method()][$request->uri->pathinfo])) {
			return new Route($request, $this->routes[$request->method()][$request->uri->pathinfo]);
		}

		return new Route($request);
	}

}