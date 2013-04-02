<?php namespace Xinix\Neph\Crud;

use \Neph\Core\View;

class Crud {

	var $id;
	var $columns = array();
	var $show_checkbox = true;
	var $excluded_columns = array('id', 'status', 'created_by', 'created_time', 'updated_by', 'updated_time');

	function __construct($config = '') {
		foreach($config as $k => $v) {
			$this->$k = $v;
		}

		$this->id = uniqid('grid-');
	}

	function grid($entries) {
		return View::instance('file://'.__DIR__.'/views/crud/grid.php')->render(array(
			'self' => $this,
			'entries' => $entries,
			));
	}

	function form() {
		return View::instance('file://'.__DIR__.'/views/crud/form.php')->render(array(
			'self' => $this,
			));
	}
}