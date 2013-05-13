<?php namespace Neph\Core;

class Console {
	static $display = true;
	static $write = true;

	static function log() {
		static::l('LOG', func_get_args());
	}

	static function error() {
		static::l('ERROR', func_get_args());
	}

	static function l($severity, $data) {
		$backtrace = debug_backtrace();

		$d = date('Y-m-d H:i:s');
		if (!is_cli() && static::$display) {
			echo '<pre>';
		}

		$line = '';

		$line .= $severity.' '.$d.'';
		$line .= str_replace(dirname(dirname($_SERVER['SCRIPT_FILENAME'])), '', $backtrace[1]['file']).':'.$backtrace[1]['line']."\n";

		foreach($data as $k => $row) {
			$line .= "#$k: ".print_r($row, 1);
			$line .= "\n";
		}

		if (static::$write) {
			$log = Neph::path('storage').'logs/'.Neph::site().'-'.date('Ymd').'.log';
			if (!file_exists(dirname($log))) mkdir(dirname($log), 0777, true);
			$f = fopen($log, 'a');
			fputs($f, $line);
			fclose($f);
		}

		if (static::$display) echo $line;

		if (!is_cli() && static::$display) {
			echo '</pre>';
		}
	}
}