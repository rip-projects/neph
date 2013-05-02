<?php namespace Neph\Core;

use \Neph\Core\Event;

class URL {
	static function theme($uri) {
		return static::base().'themes/'.Config::get('config.theme').'/'.$uri;
	}

	static function vendor($uri) {
		return static::base().'vendor/'.$uri;
	}

	static function base() {
		return Config::get('config.url');
	}

	static function site($uri = '') {
		// if (empty($uri)) return Config::get('config.url');

		if (preg_match('#^[a-z]+:\/\/#', $uri)) return $uri;

		return Config::get('config.url').Config::get('config.index').$uri;
	}

	static function redirect($uri) {
		Event::emit('response.pre_send');
		Event::emit('response.send');

		header('Location: '.static::site($uri));
		exit;
	}
}
