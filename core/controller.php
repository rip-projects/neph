<?php namespace Neph\Core;

class Controller {
	protected static $default;
	protected static $dependencies = array();
	protected static $registries = array();

	protected $request;

	public static function register($name, $ns = '') {
		static::$registries[$name] = $ns;
	}

	public static function get_class($module, $class = '', $class_type = 'controller') {

		if (empty($class)) {
			$class = $module;
		}

		$short_class_name = ($class_type == 'controller') ? $class.'_'.$class_type : $class;

		if (isset(static::$registries[$module])) {

			$full_class_name = static::$registries[$module].'\\'.$short_class_name;

			if (Loader::load($full_class_name)) {
				return $full_class_name;
			}

		}

		if (is_readable($f = Neph::path('site').Neph::site().'/modules/'.$module.'/'.$short_class_name.'.php') && require $f) {
			static::$dependencies = array_merge(static::$dependencies, array($module => Neph::path('site').Neph::site().'/modules/'.$module.'/'));
			return $short_class_name;
		}

		if (in_array($class_type, array('controller', 'model'))) {
			if ($full_class_name = Config::get('config.default_'.$class_type)) {
				if ($class_type == 'controller') {
					static::$dependencies[$module] = Neph::path('site').Neph::site().'/modules/'.$module.'/';
				}
				return $full_class_name;
			}
		}
	}

	public static function load($module) {
		$controller = Event::until('controller.load', array('module' => $module));
		if (!empty($controller)) {
			static::$dependencies = array_merge(static::$dependencies, Loader::get_dependencies());
			return $controller;
		}

		$class = static::get_class($module);
		if (!$class) {
			static::$dependencies[$module.'_controller'] = Neph::path('site').Neph::site().'/modules/'.$module.'/';
			$class = (DB::check($module)) ? '\\Xinix\\Neph\\Crud\\Crud_Controller' : '\\Neph\\Core\\Controller';
		}

		$controller = new $class;
		static::$dependencies = array_merge(static::$dependencies, Loader::get_dependencies());

		return $controller;
	}

	static function get_resource_file($uri, $module = '') {
		if (!$module) {
			foreach(static::$dependencies as $path) {
				if (is_readable($path.$uri)) {
					return $path.$uri;
				}
			}

			if (is_readable(Neph::path('site').Neph::site().$uri)) {
				return Neph::path('site').Neph::site().$uri;
			}

			if (is_readable(Neph::path('sys').$uri)) {
				return Neph::path('sys').$uri;
			}
		} else {
			if (is_readable(static::$module_paths[$module].$uri)) {
				return static::$module_paths[$module].$uri;
			}
		}

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
			$view = static::get_resource_file('/views/'.$request->uri->segments[2].'.php');
			if (empty($view)) {
				return Response::error(404);
			}

			$response = '';
		}

		return $response;
	}
}