<?php namespace Xinix\Neph\Crud;

use \Neph\Core\URL;
use \Neph\Core\DB;
use \Neph\Core\DB\ORM\Model;
use \Neph\Core\Event;
use \Neph\Core\Controller;
use \Neph\Core\Session;
use \Neph\Core\Cookie;
use \Neph\Core\Request;


class Crud_Controller extends Controller {
	var $name;
	var $crud;

	function __construct() {
		if (empty($this->name)) {
			$name = explode('_', class_basename($this));
			$this->name = strtolower($name[0]);
			if ($this->name == 'crud') {
				$this->name = Request::instance()->uri->segments[1];
			}
		}

		if (DB::check($this->name)) {
			$this->crud = $crud = new Crud($this->crud_config());

			Event::on('router.post_execute', function($data) use ($crud) {
				if (is_array($data['response']) || empty($data['response'])) {
					$data['response']['crud'] = $crud;
				}
			});
		}
	}

	function crud_config() {
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

	function _invoke($method, $key, $args = array()) {
		if (method_exists($this, $fn = strtolower($method).'_'.$key)) {
			return call_user_func_array(array($this, $fn), $args);
		} else {
			$fn = 'action_'.$key;
			return call_user_func_array(array($this, $fn), $args);
		}
	}

	function action_index($id = 0) {
		if (!$this->request->is_rest()) {
			if ($this->crud) {
				URL::redirect('/'.$this->name.'/entries');
			} else {
				return;
			}
		}

		$method = $this->request->method();

		switch ($method) {
			case 'GET':
				if ($id) {
					return $this->_invoke($method, 'detail', array($id));
				} else {
					return $this->_invoke($method, 'entries');
				}
				break;
			case 'POST':
			case 'PUT':
				$method = 'POST';
				if ($id) {
					return $this->_invoke($method, 'edit', array($id));
				} else {
					return $this->_invoke($method, 'add');
				}
				break;
		}
	}

	function get_detail($id) {
		$data = array();
		$data['publish'] = DB::table($this->name)->find($id);
		return $data;
	}

	function get_entries() {
		$data = array();
		$data['publish']['entries'] = DB::table($this->name)->get();
		return $data;
	}

	function post_add() {
		$data = $this->request->data();
		foreach ($data as $key => &$value) {
			if (empty($value)) unset($data[$key]);
		}
		$id = DB::table($this->name)->insert_get_id($data);
		URL::redirect('/'.$this->name.'/'.$id);
		// $data = array(
		// 	'publish' => DB::table($this->name)->find($id),
		// );
		// return $data;
	}


	function post_edit($id) {
		$data = $this->request->data();
		unset($data['id']);
		DB::table($this->name)->where('id', '=', $id)->update($data);
		$data = array(
			'publish' => DB::table($this->name)->find($id),
		);
		return $data;
	}

	function execute($request) {
		$this->request = $request;

		if ($request->is_rest()) {

			if (is_numeric($request->uri->segments[2])) {
				return $this->action_index(intval($request->uri->segments[2]));
			} else {
				return $this->action_index();
			}
		}

		return parent::execute($request);
	}
}