<?php namespace Neph\Core;

class Controller {

	static function load($module) {
		$controller = Event::until('controller.load', array('module' => $module));
		if (!empty($controller)) {
			return $controller;
		}

		$class = Loader::module($module);
		if (!empty($class)) {
			$controller = new $class;
			return $controller;
		}
	}

	static function register($module) {
		Loader::register_module($module);
	}

	function execute($request, $method) {
		$params = array_slice($request->uri->segments, 3);

		Event::emit('router.pre_execute', array(
			'params' => &$params,
			));

		$view = Loader::resource_file('/views/'.$method.'.php');
		if (empty($view)) {
			return Response::error(404);
		}

		$response = '';

		Event::emit('router.post_execute', array(
			'response' => &$response,
			));
		return Response::instance($response);
	}
}