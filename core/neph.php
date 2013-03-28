<?php namespace Neph\Core;

// error_reporting(0);

define('NEPH_START', microtime(true));

ob_start('mb_output_handler');

class Neph {
	static $_site;

	static function path($p) {
		global $NEPH_CONFIG;

		return ((isset($NEPH_CONFIG[$p])) ? $NEPH_CONFIG[$p] : $def_config[$p]);
	}

	static function site() {
		global $NEPH_CONFIG;

		if (static::$_site) {
			return static::$_site;
		}

		foreach($NEPH_CONFIG['sites'] as $url => $site) {
			$pattern = '#'.$url.'#';
			if (preg_match($pattern, full_url())) {
				return static::$_site = $site;
			}
		}
		return static::$_site = 'ROOT';
	}

	static function init() {
		$start_file = static::path('site').static::site().'/start.php';
		if (is_readable($start_file)) {
			include $start_file;
		}

		// Starting the routing activity
		Response::$default = Router::route();
		$success = Response::$default->send();

		if (is_cli()) {
			echo "\n";
		}

		if (!$success) {
			exit(1);
		}
	}
}

$cwd = getcwd();
$def_config = array(
	'site' => $cwd.'/../sites/',
	'sys' => $cwd.'/../core/',
	'data' => $cwd.'/../data/',
	'vendor' => $cwd.'/../vendor/',
);
$NEPH_CONFIG = $NEPH_CONFIG + $def_config;

require Neph::path('sys').'functions.php';
require Neph::path('sys').'event.php';
require Neph::path('sys').'loader.php';
spl_autoload_register(array('Neph\\Core\\Loader', 'load'));

set_exception_handler(function($e) {
	require_once Neph::path('sys').'error.php';
	\Neph\Core\Error::exception($e);
});

set_error_handler(function($code, $error, $file, $line) {
	require_once Neph::path('sys').'error.php';
	\Neph\Core\Error::native($code, $error, $file, $line);
});

register_shutdown_function(function() {
	require_once Neph::path('sys').'error.php';
	\Neph\Core\Error::shutdown();
});


Loader::directories(Neph::path('vendor'));
Loader::namespaces(array('Neph\\Core' => Neph::path('sys')));

Neph::init();
