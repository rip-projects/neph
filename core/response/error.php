<?php namespace Neph\Core\Response;

use \Neph\Core\Response;
use \Neph\Core\View;
use \Neph\Core\Console;

class Error extends Response {
	var $status;
	var $message;
	var $data;

	function __construct($status, $message, $data) {
		$this->status = $status;
		$this->message = ($message) ? $message : static::$STATUS[$status];
		$this->errors = array($this->message);
		$this->data = $data;
	}

	function render() {
		while (ob_get_level() > 0) {
      		ob_end_clean();
   		}
		if (is_cli()) {
			ob_start();
			Console::error($this->status.' '.$this->errors[0]);
			if (isset($this->data['exception'])) {
				Console::error($this->data['exception']->getMessage(), $this->data['exception']->getTraceAsString());
			}
			echo "\n";
			debug_print_backtrace();
			$this->content = ob_get_clean();
		} else {
			$this->content = View::instance('/error/'.$this->status)->render((array) $this);
		}
		return $this->content;
	}
}