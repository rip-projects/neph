<?php namespace Xinix\Neph\Crud;

use \Neph\Core\URL;
use \Neph\Core\DB;
use \Neph\Core\DB\ORM\Model;
use \Neph\Core\Event;
use \Neph\Core\Controller;

class Crud_Controller extends Controller {
	var $name;
	var $crud;

	function __construct() {
		if (empty($this->name)) {
			$name = explode('_', class_basename($this));
			$this->name = strtolower($name[0]);
		}

		$this->crud = $crud = new Crud($this->grid_config());

		Event::on('router.post_execute', function($data) use ($crud) {
			$data['response']['crud'] = $crud;

		});
	}

	function grid_config() {
		static $config;

		if (!$config) {
			$meta_columns = DB::columns($this->name);
			$config = array(
				'columns' => array_keys($meta_columns),
				'meta_columns' => $meta_columns,
			);
		}
		return $config;
	}

	function action_index() {
		URL::redirect('/'.$this->name.'/entries');
	}

	function action_entries() {
		$data = array();
		$data['publish']['entries'] = DB::table($this->name)->get();
		return $data;
	}

	function get_add() {
		$data = array();
		return $data;
	}
}