<?php namespace Neph\Core;

class Loader {

	static public $aliases = array();

	static private $namespaces = array();
	static private $directories = array();

	static private $dependencies = array();

	static function namespaces($mappings, $append = '\\') {
		$mappings = static::format_mappings($mappings, $append);
		static::$namespaces = array_merge($mappings, static::$namespaces);
	}

	static function load($class) {
		if (isset(static::$aliases[$class])) {
			return class_alias(static::$aliases[$class], $class);
		}

		if (ends_with(strtolower($class), '_controller')) {
			return static::load_controller($class);
		}

		foreach (static::$namespaces as $namespace => $directory) {
			if (starts_with($class, $namespace)) {
				return static::load_namespaced($class, $namespace, $directory);
			}
		}

		if ($try = static::load_psr($class)) {
			return $try;
		}
	}

	protected static function load_controller($class) {
		$dirpath = explode('\\', $class);
		$class_name = array_splice($dirpath, -1);
		$dirpath = strtolower(implode('/', $dirpath));
		$class_name = strtolower($class_name[0]);
		$file = $dirpath.'/'.$class_name;

		foreach ((array) static::$directories as $directory) {
			if (is_readable($path = $directory.$file.'.php')) {
				if (is_readable($path)) {
					static::$dependencies[$class_name] = $directory.$dirpath;
					if (require $path) {
						return $directory.$dirpath;
					}
				}
				return;
			}
		}
	}

	public static function get_dependencies() {
		return static::$dependencies;
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
				return require $path;
			} elseif (is_readable($path = $directory.$file.'.php')) {
				return require $path;
			}
		}
	}

	public static function directories($directory) {
		$directories = static::format($directory);
		static::$directories = array_unique(array_merge(static::$directories, $directories));
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