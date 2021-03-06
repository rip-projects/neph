<?php namespace Neph\Core;

class Request {

	static $instance;
	static $route;

	public $uri;
	protected $accept = '';
	protected $location = array();

	private $data;

	static function instance() {
		if (!static::$instance) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	function __construct() {
		if (is_cli()) {
			$uri = array();
			foreach($_SERVER['argv'] as $argv) {
				$uri[] = $argv;
			}
			$uri[0] = '';
			$uri = implode('/', $uri);
		} else {
			$uri = (empty($_SERVER['PATH_INFO'])) ? '/' : $_SERVER['PATH_INFO'];
		}
		$this->uri = new URI($uri);
	}

	function forward($uri) {
		$this->uri = new URI($uri);
	}

	function method() {
		return (is_cli()) ? 'CLI' : $_SERVER['REQUEST_METHOD'];
	}

	function cookie($key, $value = '__GET__') {
		if ($value === '__GET__') {
			return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : NULL;
		}
	}

	function is_rest() {
		if (is_cli()) return false;
		elseif ($this->uri->extension == 'json') return true;

		$rest_content_types = array('application/json');
		if (!empty($_SERVER['CONTENT_TYPE']) && in_array($_SERVER['CONTENT_TYPE'], $rest_content_types)) return true;
		$accept = $this->accept();
		if (in_array($accept[0]['mime'], $rest_content_types)) return true;
		return false;
	}

	function accept() {
		if (empty($this->accept)) {
			$accepting = explode(',', $_SERVER['HTTP_ACCEPT']);
			foreach ($accepting as &$value) {
				$exploded = explode(';', $value);
				$value = array(
					'mime' => trim($exploded[0]),
					'priority' => (empty($exploded[1]) ? 1 : doubleval(substr($exploded[1], 2))),
				);
			}

			usort($accepting, function ($a, $b) {
			    return $a['priority'] <= $b['priority'];
			});
			$this->accept = $accepting;
		}
		return $this->accept;
	}

	function language() {
		if (empty($this->language)) {
			if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$this->language = array();
			} else {
				$accepting = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				foreach ($accepting as &$value) {
					$exploded = explode(';', $value);
					$lang = explode('-', trim($exploded[0]));
					$value = array(
						'lang' => $lang[0],
						'country' => (isset($lang[1])) ? $lang[1] : '',
						'priority' => (empty($exploded[1]) ? 1 : doubleval(substr($exploded[1], 2))),
					);
				}
				usort($accepting, function ($a, $b) {
				    return $a['priority'] <= $b['priority'];
				});
				$this->language = $accepting;
			}
		}
		return $this->language;
	}

	function data() {
		if (!isset($this->data)) {
			$content_type = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : '';
			switch ($content_type) {
				case 'application/json':
					$this->data = json_decode(file_get_contents('php://input'), true);
				default:
					$this->data = $_POST;
			}
		}
		return $this->data;
	}

	function set_data($data) {
		$this->data = $data;
	}

}