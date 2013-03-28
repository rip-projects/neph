<?php namespace Xinix\Neph\Grid;

use \Neph\Core\View;

class Grid {

	var $config;

	static function instance($config) {
		return new Grid($config);
	}

	function __construct($config = '') {
		$this->config = $config;
	}

	function show($entries) {
		return View::instance('file://'.__DIR__.'/views/show.php')->render(array(
			'grid' => (array) $this,
			'entries' => $entries,
			));
	}
}