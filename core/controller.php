<?php namespace Neph\Core;

class Controller {
	static private $modules = array();

	static function load($module) {
		$ns = static::get($module);
		if (empty($ns)) {
			return '';
		}
		$class = Loader::module($ns, $module);
		if (!empty($class)) {
			$controller = new $class;
			return $controller;
		}
	}

	static function get($module) {
		if (isset(static::$modules[$module])) return static::$modules[$module];

		if (is_readable(Neph::path('site').Neph::site().'/modules/'.$module)) return $module;
	}

	static function has($module) {
		$dir = static::get($module);
		return (!empty($dir));
	}

	static function register($module) {
		$exploded = explode('\\', $module);
		$module_name = strtolower($exploded[count($exploded)-1]);
		static::$modules[$module_name] = $module;
	}

	function action_index() {

	}
}