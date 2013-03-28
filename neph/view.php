<?php namespace Neph;

class View {
	static function load($uri, $arg_data = '') {
		if (empty($arg_data['_pre_data'])) $arg_data['_pre_data'] = '';
		if (empty($arg_data['content'])) $arg_data['content'] = '';

		$view_file = Loader::resource_file('/views'.$uri.'.php');

		if (!$view_file) {
			return $arg_data['_pre_data'].$arg_data['content'];
			// throw new \Exception("Shame on you, you don't have the view: $uri");
		} else {
			if (!empty($arg_data)) {
				extract($arg_data);
			}

			ob_start();
			include $view_file;
			$content = ob_get_contents();
			ob_end_clean();

			if (!empty($arg_data['_pre_data'])) {
				$content = $arg_data['_pre_data'].$content;
			}

			if (!isset($arg_data['_response']) || $arg_data['_response']->layout !== $uri) {
				Event::emit('view.filter_content', array(
					'content' => &$content,
				));
			}
		}
		if (isset($arg_data['_response']) && $arg_data['_response']->layout !== $uri) {
			$arg_data['content'] = $content;
			$content = static::load($arg_data['_response']->layout, $arg_data);
		}

		return $content;
	}
}