<?php namespace NephModules\Crud;

use \Neph\Core\Console;
use \Neph\Core\URL;
use \Neph\Core\DB;
use \Neph\Core\Event;

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

		$this->columns = DB::table($this->name)->columns();

// 		Event::on('router.post_execute', function($data) {
// 			Event::on('view.filter_content', function($d) use ($data) {
// 				$d['content'] = $d['content'].'
// <script type="text/javascript">
// 	window.CRUD = {
// 		publish: '.json_encode($data['response']['publish']).',
// 		columns: '.json_encode($data['response']['columns']).'
// 	};
// </script>';
// 			});
// 		});
	}
	function action_index() {
		URL::redirect('/'.$this->name.'/entries');
	}

	function action_entries() {
		$data = array();
		$data['publish']['entries'] = DB::table($this->name)->get();
		$data['grid_config']  = array(
			'columns' => array_map(function($row) {
				return $row->field;
			}, $this->columns),
		);
		return $data;
	}
}