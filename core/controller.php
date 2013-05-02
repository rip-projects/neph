<?php namespace Neph\Core;

class Controller {
	protected static $default;
	protected static $dependencies = array();
	protected static $registries = array();

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

	public static function get_resource_file($uri) {
		foreach(static::$dependencies as $path) {
			if (is_readable($path.$uri)) {
				return $path.$uri;
			}
		}

		return Neph::get_resource_file($uri);
	}

	public function __construct() {
		$self = $this;

		Event::on('route.pre_call', function() use ($self) {
			if (!$self->authorized()) {
				return Response::redirect('/login');
			}
		});
	}

	protected function authorized() {
        if (Auth::check()) {
        	return true;
        }

        return false;
    }
}