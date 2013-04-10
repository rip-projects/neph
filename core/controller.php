<?php namespace Neph\Core;

class Controller {
	protected $request;

	static function load($module) {
		$controller = Event::until('controller.load', array('module' => $module));
		if (!empty($controller)) {
			return $controller;
		}

		$class = Loader::module($module);

		if (!empty($class)) {
			$controller = new $class;
			return $controller;
		} else {
			$class = Config::get('config.default_view');
			if (empty($class)) {
				if (DB::check($module)) {
					return new \Xinix\Neph\Crud\Crud_Controller();
				} else {
					return new \Neph\Core\Controller();
				}
			} else {
				$controller = new $class;
				return $controller;
			}
		}
	}

	static function register($name, $module = '') {
		Loader::register_module($name, $module);
	}

	function execute($request) {
		$this->request = $request;
		$params = array_slice($request->uri->segments, 3);

		if (method_exists($this, $fn = $request->method().'_'.$request->uri->segments[2])) {
			$response = call_user_func_array(array($this, $fn), $params);
		} elseif (method_exists($this, 'action_'.$request->uri->segments[2])) {
			$response = call_user_func_array(array($this, 'action_'.$request->uri->segments[2]), $params);
		} else {
			// get view file name just to check whether view is exist
			$view = Loader::resource_file('/views/'.$request->uri->segments[2].'.php');
			if (empty($view)) {
				return Response::error(404);
			}

			$response = '';
		}

		return $response;
	}
}