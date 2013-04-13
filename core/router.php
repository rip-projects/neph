<?php namespace Neph\Core;

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

		if ($request->uri->pathinfo === '/') {
			Request::instance()->forward('/home/index');
			return $this->route(Request::instance());
		} elseif (empty($request->uri->segments[2])) {
			Request::instance()->forward('/'.$request->uri->segments[1].'/index');
			return $this->route(Request::instance());
		}


		try {
			$controller = Controller::load($request->uri->segments[1]);
		} catch(\Neph\Core\LoaderException $e) {
			$controller = null;
		} catch(\Exception $e) {
			return Response::error(500, $e->getMessage(), array('exception' => $e));
		}

		if ($controller) {
			$params = array_slice($request->uri->segments, 3);

			Event::emit('router.pre_execute', array(
				'segments' => $request->uri->segments,
				'params' => &$params,
				));


			if (method_exists($controller, 'execute')) {
				$response = $controller->execute($request);
			} else {

				if (method_exists($controller, $request->method().'_'.$request->uri->segments[2])) {
					$action = $request->method().'_'.$request->uri->segments[2];
				} elseif (method_exists($controller, 'action_'.$request->uri->segments[2])) {
					$action = 'action_'.$request->uri->segments[2];
				} else {
					return Response::error(404);
				}
				$response = call_user_func_array($fn, $params);
			}

			Event::emit('router.post_execute', array(
				'response' => &$response,
				));

			return Response::instance($response);

		}

		// 404
		return Response::error(404);
	}
}

Router::init();
