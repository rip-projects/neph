<?php namespace Neph\Core;

class Event {

	static $events = array();

	static function emit($event, $data = array()) {
		$results = array();
		if (!empty(static::$events[$event])) {
			foreach (static::$events[$event] as $fn) {
				if (is_callable($fn)) {
					$results[] = $fn($data);
				}
			}
		}
		return $results;
	}

	static function until($event, $data = array()) {
		if (!empty(static::$events[$event])) {
			foreach (static::$events[$event] as $fn) {
				if (is_callable($fn)) {
					$result = $fn($data);
					if (!empty($result)) {
						return $result;
					}
				}
			}
		}
		return;
	}

	static function on($event, $fn) {
		static::$events[$event][] = $fn;
	}
}