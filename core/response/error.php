<?php namespace Neph\Core\Response;

use \Neph\Core\Response;
use \Neph\Core\View;

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
		if (is_cli()) {
			Console::error($this->status.' '.$this->errors[0]);
			if (isset($this->data['exception'])) {
				Console::error($this->data['exception']->getMessage(), $this->data['exception']->getTraceAsString());
			}
			return;
		} else {
			while (ob_get_level() > 0) {
	      		ob_end_clean();
	   		}
			$this->content = View::instance('/error/'.$this->status)->render((array) $this);
		}
		return $this->content;
	}
}