<?php namespace Neph\Response;

class Error extends \Neph\Response {
	static $STATUS = array(
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',

		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		303	=> 'See Other',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',

		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',
		422	=> 'Unprocessable Entity',

		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);

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
			$this->content = \Neph\View::load('/error/'.$this->status, (array) $this);
		}
		return $this->content;
	}
}