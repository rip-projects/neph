<?php namespace NephModules\Crud;

use \Neph\Console;
use \Neph\URL;
use \Neph\DB;

class Crud_Controller {
	var $name;
	var $model;

	function __construct() {
		if (empty($this->name) || empty($this->model)) {
			$exploded = explode('\\', strtolower(get_called_class()));
			$exploded = explode('_controller', $exploded[count($exploded) - 1]);
			if (empty($this->name)) $this->name = $exploded[0];
			if (empty($this->model)) $this->model = $exploded[0];
		}
	}
	function action_index() {
		URL::redirect('/'.$this->name.'/entries');
	}

	function action_entries() {
		$data = array();
		$data['publish']['entries'] = DB::table($this->name)->get();
		return $data;
	}
}