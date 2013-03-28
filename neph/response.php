<?php namespace Neph;

class Response {
	static public $default;

	var $status = 200;
	var $content;
	var $uri;
	var $layout;
	var $view = '';

	static function error($status = 500, $message = '', $data = '') {
		return new \Neph\Response\Error($status, $message, $data);
	}

	static function instance($response) {
		$event_response = Event::until('response.instance', array(
			'response' => $response
			));

		if ($event_response instanceof Response) {
			return $event_response;
		}

		if ($response instanceof Response) {
			return $response;
		}

		if (is_string($response)) {
			return new static(array('_pre_data' => $response));
		}

		return new static((array) $response);
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
		$this->data['_response'] = $this;

		Event::emit('response.pre_render', array(
			'response' => $this
			));
		
		$view = (empty($this->view)) ? '' : $this->view;
		if (empty($view)) {
			$view = '/'. (isset($this->uri->segments[2]) ? $this->uri->segments[2] : 'index');
		}

		$this->data['_pre_data'] = ob_get_clean().(isset($this->data['_pre_data']) ? $this->data['_pre_data'] : '');

		$this->content = Event::until('response.render', array(
			'view' => $view, 
			'data' => $this->data,
			));
		if ($this->content === NULL) {
			$this->content = View::load($view, $this->data);
		}

		return $this->content;
	}

	function send_headers() {
		if (!is_cli() && (empty($this->status) || $this->status != 200)) {
			$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
			if (strpos(php_sapi_name(), 'cgi') === 0) {
				header('Status: '.$this->status.' '.$this->errors[0], TRUE);
			} else {
				header(($server_protocol ? $server_protocol : 'HTTP/1.1').' '.$this->status.' '.$this->errors[0], TRUE, $this->status);
			}
		}
	}

	function send() {
		$this->send_headers();
		echo $this->render();
	}
}
