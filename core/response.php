<?php namespace Neph\Core;

class Response {
	static public $default;

	var $status = 200;
	var $content;
	var $uri;
	var $layout;
	var $view = '';
	var $data;
	var $pre_data = '';
	var $post_data = '';

	static function error($status = 500, $message = '', $data = '') {
		return new \Neph\Core\Response\Error($status, $message, $data);
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

		return new static($response);
	}

	function __construct($data = '') {
		if (is_string($data)) {
			$this->pre_data = $data;
		} else {
			$this->data = (array) $data;
		}
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

		if (empty($this->view)) {
			$this->view = '/'. (isset($this->uri->segments[2]) ? $this->uri->segments[2] : 'index');
		}

		$this->pre_data = ob_get_clean().$this->pre_data;

		$this->content = Event::until('response.render', array(
			'response' => &$this,
			));

		if (Loader::resource_file('/views'.$this->view.'.php')) {
			$view = View::instance($this->view)
				->prepend($this->pre_data)
				->append($this->post_data);

			if ($this->layout) {
				$view->layout($this->layout);
			}
			$this->content = $view->render($this->data);
		} else {
			$this->content = $this->pre_data;
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
