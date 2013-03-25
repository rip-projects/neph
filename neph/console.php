<?php namespace Neph;

class Console {
	static function log() {
		static::l('LOG', func_get_args());
	}

	static function error() {
		static::l('ERROR', func_get_args());
	}

	static function l($severity, $data) {
		$d = date('Y-m-d H:i:s');
		if (!is_cli()) {
			echo '<pre>';
		}
		foreach($data as $k => $row) {
			echo $severity.' '.$d.' ('.$k.') ';
			echo print_r($row, 1);
			echo "\n";
		}
		if (!is_cli()) {
			echo '</pre>';
		}
	}
}