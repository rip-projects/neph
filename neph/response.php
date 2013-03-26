<?php namespace Neph;

class Response {
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

	static public $default;

	var $status = 200;
	var $errors;
	var $data;
	var $content;
	var $uri;
	var $layout;

	static function error($status = 500, $message = '', $data = '') {
		$resp = new Response();
		$resp->status = $status;
		$resp->errors = array(($message) ? $message : static::$STATUS[$status]);
		$resp->data = $data;
		return $resp;
	}

	static function instance($response) {
		if ($response instanceof Response) {
			return $response;
		} elseif (is_string($response)) {
			return new static(array('content' => $response));
		} else {
			return new static((array) $response);
		}

	}

	function __construct($data = '') {
		$this->data = $data;
		$this->uri = Request::instance()->uri;
		$this->layout = Config::get('config/layout');
	}

	function view($view) {
		$this->view = $view;
		return $this;
	}

	function render() {
		$this->data['_pre_data'] = ob_get_clean();
		$this->data['_response'] = $this;
		$view = (empty($this->view)) ? '' : $this->view;
		if (empty($view)) {
			$view = '/'. (isset($this->uri->segments[2]) ? $this->uri->segments[2] : 'index');
		}
		if ($this->status == 200) {
			$this->content = View::load($view, $this->data);
		} else {
			if (is_cli()) {
				Console::error($this->status.' '.$this->errors[0]);
				if (isset($this->data['exception'])) {
					Console::error($this->data['exception']->getMessage(), $this->data['exception']->getTraceAsString());
				}
				return;
			} else {
				$this->content = View::load('/error/'.$this->status, $this->data);
			}
		}
	}

	function send() {
		if ($this->status != 200 && !is_cli()) {
			$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
			if (strpos(php_sapi_name(), 'cgi') === 0) {
				header('Status: '.$this->status.' '.$this->errors[0], TRUE);
			} else {
				header(($server_protocol ? $server_protocol : 'HTTP/1.1').' '.$this->status.' '.$this->errors[0], TRUE, $this->status);
			}
		}
		$this->render();
		echo $this->content;

		return ($this->status == 200);
	}
}