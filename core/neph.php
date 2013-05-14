<?php namespace Neph\Core;

use \Neph\Core\DB\ORM\Manager;

define('NEPH_START', microtime(true));
define('MB_STRING', (int) function_exists('mb_get_info'));

class Neph {
	static $_site;

	static function get_resource_file($uri) {
		if (is_readable(Neph::path('site').Neph::site().$uri)) {
			return Neph::path('site').Neph::site().$uri;
		}

		if (is_readable(Neph::path('sys').$uri)) {
			return Neph::path('sys').$uri;
		}
	}

	static function path($p) {
		global $NEPH_CONFIG;

		return ((isset($NEPH_CONFIG[$p])) ? $NEPH_CONFIG[$p] : $def_config[$p]);
	}

	static function site() {
		global $NEPH_CONFIG;

		if (static::$_site) {
			return static::$_site;
		}

		if (!is_cli()) {
			foreach($NEPH_CONFIG['sites'] as $url => $site) {
				$pattern = '#'.$url.'#';
				if (preg_match($pattern, full_url())) {
					return static::$_site = $site;
				}
			}
			return static::$_site = 'ROOT';
		}

		return static::$_site = get($_SERVER, 'SITE', 'ROOT');
	}

	static function load() {
		global $NEPH_CONFIG;


		// error_reporting(0);

		ob_start('mb_output_handler');

		$cwd = getcwd();
		$NEPH_CONFIG = array_merge(array(
			'site' => $cwd.'/../sites/',
			'sys' => $cwd.'/../core/',
			'storage' => $cwd.'/../storage/',
			'vendor' => $cwd.'/../vendor/',
		), $NEPH_CONFIG);

		require Neph::path('sys').'functions.php';
		require Neph::path('sys').'event.php';
		require Neph::path('sys').'loader.php';
		spl_autoload_register(array('Neph\\Core\\Loader', 'load'));

		if (is_cli()) {
			echo static::site().': CLI sequence started at '.date('Y-m-d H:i:s', NEPH_START)."\n";
		}

		if (!NEPH_DEBUG) {

			// FIXME fix this exception handler for uncaught no module exist error
			set_exception_handler(function($e) {
				require_once Neph::path('sys').'error.php';
				Error::exception($e);
			});

			set_error_handler(function($code, $error, $file, $line) {
				require_once Neph::path('sys').'error.php';
				Error::native($code, $error, $file, $line);
			});

			register_shutdown_function(function() {
				require_once Neph::path('sys').'error.php';
				Error::shutdown();
			});

		}

		Loader::directories(Neph::path('vendor'));
		Loader::namespaces(array('Neph\\Core' => Neph::path('sys')));

		try {
			// on response send try to save session
			if (!is_cli() && Config::get('session.default', '') !== '') {
				Event::on('response.send', function() {
					Session::save();
				});
			}

			// initialize language
			Lang::load();

			// register orm manager
			if (!IoC::registered('orm.manager')) {
				IoC::singleton('orm.manager', function() {
					return new Manager;
				});
			}

			// read custom site start procedure
			$start_file = static::path('site').static::site().'/start.php';
			if (is_readable($start_file)) {
				include $start_file;
			}

			/**
			 * starting the routing activity and get the default response from
			 * router's route
			 */
			Request::$route = Router::instance()->route();
			Response::$instance = Request::$route->call();
		} catch(\Exception $e) {
			Response::$instance = Response::error(500, $e->getMessage(), array('exception' => $e));
		}

		/**
		 * render the response to send later
		 */
		Response::$instance->render();

		Event::emit('response.pre_send');
		Event::emit('response.send');

		$success = Response::$instance->send();

		if (is_cli()) {
			$end = microtime(true);
			echo static::site().": CLI sequence finished at ".date('Y-m-d H:i:s', $end).", elapsed time ".sprintf('%.3f', $end - NEPH_START)."s\n";
		}

		if (!$success) {
			exit(1);
		}
	}
}

Neph::load();
