<?php namespace Neph\Core;

class View {
	var $pre_data = '';
	var $post_data = '';
	var $layout;

	static function instance($uri) {
		return new View($uri);
	}

	function __construct($uri) {
		$this->uri = $uri;
		if (starts_with($uri, 'file://')) {
			$this->file = substr($uri, 7);
			if (!is_readable($this->file)) {
				$this->file = '';
			}
		} else {
			$this->file = Loader::resource_file('/views'.$this->uri.'.php');
		}
		if (!$this->file) {
			throw new \Exception("Shame on you, you don't have the view: ".$this->uri);
		}
	}

	function render($arg_data) {
		
		if (!empty($arg_data)) {
			extract($arg_data);
		}

		ob_start();
		include $this->file;
		$content = ob_get_contents();
		ob_end_clean();

		$content = $this->pre_data.$content.$this->post_data;

		if ($this->layout) {
			$content = static::instance($this->layout)->render(array('content' => $content));
		}

		return $content;
	}

	function prepend($pre_data) {
		$this->pre_data = $pre_data;
		return $this;
	}

	function append($post_data) {
		$this->post_data = $post_data;
		return $this;
	}

	function layout($layout) {
		$this->layout = $layout;
		return $this;
	}
}