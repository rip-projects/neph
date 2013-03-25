<?php namespace Neph;

class View {
	static function load($uri, $data = '') {
		$view_file = Loader::resource_file('/views'.$uri.'.php');

		if (!$view_file) {
			throw new \Exception('View not found: '.$uri);
		}

		if (!empty($data)) {
			extract($data);
		}

		ob_start();
		include $view_file;
		$content = ob_get_contents();
		ob_end_clean();

		if (!is_cli() && !empty($data['_response']->layout)) {
			$content = static::load($data['_response']->layout, array('content' => $content));
		}

		return $content;
	}
}