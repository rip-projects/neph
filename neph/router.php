<?php namespace Neph;

class Router {
	static private $instance;

	static function instance() {
		if (!static::$instance) {
			static::$instance = new RouterImpl();
		}
		return static::$instance;
	}

	static function __callStatic($method, $parameters) {
		return call_user_func_array(array(static::instance(), $method), $parameters);
	}

}

class RouterImpl {
	var $routes = array(
		'GET' => array(),
		'POST' => array(),
	);

	function init() {}

	function register($method, $key, $fn) {
		$this->routes[$method][$key] = $fn;
	}

	function get($key, $fn) {
		$this->register('GET', $key, $fn);
	}

	function route($request = '') {
		if ($request === '') {
			$request = Request::instance();
		} elseif (!$request instanceof RequestImpl) {
			Request::instance()->forward($request);
			return $this->route(Request::instance());
		}

		if (isset($this->routes[$request->method()][$request->uri->pathinfo])) {
			return $this->execute($request, $this->routes[$request->method()][$request->uri->pathinfo]);
		}

		if ($request->uri->pathinfo === '/' && Controller::has('home')) {
			Request::instance()->forward('/home/index');
			return $this->route(Request::instance());
		} elseif (empty($request->uri->segments[2])) {
			Request::instance()->forward('/'.$request->uri->segments[1].'/index');
			return $this->route(Request::instance());
		}

		$controller = Controller::load($request->uri->segments[1]);
		if ($controller) {
			$params = array_slice($request->uri->segments, 3);
			$action = $request->uri->segments[2];
			if (method_exists($controller, $request->method().'_'.$action)) {
				$action = $request->method().'_'.$action;
			} elseif (method_exists($controller, 'action_'.$action)) {
				$action = 'action_'.$action;
			} else {
				return Response::error(404);
			}
			return $this->execute($request, array($controller, $action));
		}

		// 404
		return Response::error(404);
	}

	function execute($request, $fn) {
		$params = array_slice($request->uri->segments, 3);

		Event::emit('router.pre_execute', array(
			'params' => &$params,
			));

		$response = call_user_func_array($fn, $params);

		Event::emit('router.post_execute', array(
			'response' => &$response,
			));
		return Response::instance($response);
	}
}

Router::init();
