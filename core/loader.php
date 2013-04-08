<?php namespace Neph\Core;

class Loader {

	static public $aliases = array();

	static private $namespaces = array();
	static private $directories = array();

	static private $module = '';
	static private $module_namespaces = array();
	static private $module_paths = array();

	static function namespaces($mappings, $append = '\\') {
		$mappings = static::format_mappings($mappings, $append);
		static::$namespaces = array_merge($mappings, static::$namespaces);
	}

	static function load($class) {
		if (isset(static::$aliases[$class])) {
			return class_alias(static::$aliases[$class], $class);
		}

		foreach (static::$namespaces as $namespace => $directory) {
			if (starts_with($class, $namespace)) {
				return static::load_namespaced($class, $namespace, $directory);
			}
		}

		if (ends_with(strtolower($class), '_controller')) {
			return static::load_controller($class);
		}

		if (ends_with(strtolower($class), '_model')) {
			return static::load_model($class);
		}

		if ($try = static::load_psr($class)) {
			return $try;
		}

		// foreach (static::$namespaces as $namespace => $directory) {
		// 	return static::load_namespaced('\\Neph\\'.$class, $namespace, $directory);
		// }
	}

	protected static function load_controller($class) {
		if (isset(static::$module_paths[$class])) return;

		$dirpath = explode('\\', $class);
		$class_name = array_splice($dirpath, -1);
		$dirpath = strtolower(implode('/', $dirpath));
		$class_name = strtolower($class_name[0]);
		$file = $dirpath.'/'.$class_name;

		foreach ((array) static::$directories as $directory) {
			if (is_readable($path = $directory.$file.'.php')) {
				static::$module_paths[$class] = $directory.$dirpath;
				return require $path;
			}
		}
	}

	protected static function load_namespaced($class, $namespace, $directory) {
		return static::load_psr(substr($class, strlen($namespace)), $directory);
	}

	protected static function load_psr($class, $directory = null) {
		$file = str_replace(array('\\', '_'), '/', $class);

		$directories = $directory ?: static::$directories;

		$lower = strtolower($file);

		foreach ((array) $directories as $directory) {
			if (is_readable($path = $directory.$lower.'.php')) {
				// echo($class."<br/>\n");
				return require $path;
			} elseif (is_readable($path = $directory.$file.'.php')) {
				return require $path;
			}
		}
	}

	public static function module($module) {
		static::$module = $module;

		if (isset(static::$module_namespaces[$module])) {
			$ns = static::$module_namespaces[$module];
			if (!$ns) {
				return '';
			}

			$dir = strtolower(str_replace(array('\\', '_'), '/', $ns));

			foreach (static::$directories as $directory) {
				if (is_readable($directory.$dir)) {
					static::$module_paths[$module] = $directory.$dir;
					if (is_readable($directory.$dir.'/'. $module.'_controller.php')) {
						return $ns.'\\'.$module.'_Controller';
					} else {
						return '';
					}
				}
			}
		} elseif (is_readable($dir = Neph::path('site').Neph::site().'/modules/'.$module)) {
			static::$module_paths[$module] = $dir;
			if (is_readable($dir.'/'.$module.'_controller.php')) {
				require $dir.'/'.$module.'_controller.php';
				return $module.'_Controller';
			} else {
				return '';
			}
		} else {
			throw new \Exception('No module ['.$module.'] available!');
		}
	}

	public static function module_model($module) {
		if (isset(static::$module_namespaces[$module])) {
			$ns = static::$module_namespaces[$module];
			$dir = strtolower(str_replace(array('\\', '_'), '/', $ns));
			foreach ($directories as $directory) {
				if (is_readable($directory.$dir)) {
					if (is_readable($directory.$dir.'/'. $module.'_controller.php')) {
						return $ns.'\\'.$module;
					} else {
						return '\\Neph\\Core\\ORM\\Model';
					}
				}
			}
		} elseif (is_readable(Neph::path('site').Neph::site().'/modules/'.$module)) {
			$dir = Neph::path('site').Neph::site().'/modules/'.$module;
			if (is_readable($dir)) {
				if (is_readable($dir.'/'.$module.'.php')) {
					return $module;
				} else {
					return '\\Neph\\Core\\ORM\\Model';
				}
			}
		} else {
			throw new Exception('No module model ['.$module.'] available!');
		}
	}

	public static function register_module($module_name, $module = '') {
		static::$module_namespaces[$module_name] = $module;
	}

	public static function directories($directory) {
		$directories = static::format($directory);
		static::$directories = array_unique(array_merge(static::$directories, $directories));
	}

	public static function resource_file($uri, $module = '') {
		if (!$module) {
			foreach(static::$module_paths as $path) {
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

	protected static function format_mappings($mappings, $append) {
		foreach ($mappings as $namespace => $directory) {
			// When adding new namespaces to the mappings, we will unset the previously
			// mapped value if it existed. This allows previously registered spaces to
			// be mapped to new directories on the fly.
			$namespace = trim($namespace, $append).$append;

			unset(static::$namespaces[$namespace]);

			$formatted_dirs = static::format($directory);
			$namespaces[$namespace] = reset($formatted_dirs);
		}

		return $namespaces;
	}

	protected static function format($directories) {
		return array_map(function($directory) {
			return rtrim($directory, '/').'/';
		}, (array) $directories);
	}
}