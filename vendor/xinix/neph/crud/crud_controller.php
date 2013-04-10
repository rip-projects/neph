<?php namespace Xinix\Neph\Crud;

use \Neph\Core\URL;
use \Neph\Core\DB;
use \Neph\Core\DB\ORM\Model;
use \Neph\Core\Event;
use \Neph\Core\Controller;
use \Neph\Core\Session;
use \Neph\Core\Cookie;
use \Neph\Core\Request;
use \Xinix\Neph\Filter\Filter;
use \Xinix\Neph\Message\Message;


class Crud_Controller extends Controller {
	var $name;
	var $crud;
	var $model;
	var $filters = array();

	function __construct() {
		if (empty($this->name)) {
			$name = explode('_', class_basename($this));
			$this->name = strtolower($name[0]);
			if ($this->name === 'crud') {
				$this->name = Request::instance()->uri->segments[1];
			}
		}

		if (DB::check($this->name)) {
			$this->model = Model::factory($this->name);
			$this->crud = $crud = new Crud($this->crud_config());

			Event::on('router.post_execute', function($data) use ($crud) {
				if (is_array($data['response']) || empty($data['response'])) {
					$data['response']['crud'] = $crud;
				}
			});
		}
	}

	function filter_config() {
		return $this->filters;
	}

	function crud_config() {
		static $config;

		if (!$config) {
			$meta_columns = $this->model->columns();
			$config = array(
				'name' => $this->name,
				'columns' => array_keys($meta_columns),
				'meta_columns' => $meta_columns,
				'actions' => array(
					'edit' => '/'.$this->name.'/edit',
					'delete' => '/'.$this->name.'/delete',
				),
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
				$fn = ($id) ? 'detail' : 'entries';
				break;
			case 'POST':
			case 'PUT':
				$method = 'POST';
				$fn = ($id) ? 'edit' : 'add';
				break;
			case 'DELETE':
				$method = 'POST';
				$fn = 'delete';
				break;
		}

		if (isset($fn)) {
			// FIXME run filter for matching fn
			return $this->_invoke($method, $fn, array($id));
		}
	}

	function get_detail($id) {
		$data = array();
		$data['publish'] = $this->model->find($id);
		return $data;
	}

	function get_edit($id) {
		$data = array();
		$data['data'] = $this->model->find($id);
		return $data;
	}

	function get_delete($id) {
		$result = $this->model->delete($id);

		if ($this->request->is_rest()) {
			return true;
		} else {
			Message::success('Record deleted.');
			URL::redirect('/'.$this->name.'/entries');
		}
	}

	function get_entries() {
		$data = array();
		$data['publish']['entries'] = $this->model->all();
		return $data;
	}

	function post_add() {
		$data = $this->request->data();
		foreach ($data as $key => &$value) {
			if (empty($value)) unset($data[$key]);
		}
		$data = $this->model->create($data)->to_array();

		if ($this->request->is_rest()) {
			URL::redirect('/'.$this->name.'/'.$data['id']);
		} else {
			Message::success('Record added.');
			URL::redirect('/'.$this->name.'/detail/'.$data['id']);
		}
	}


	function post_edit($id) {
		$entry = $this->request->data();
		unset($entry['id']);
		$result = $this->model->where('id', '=', $id)->update($entry);

		if ($this->request->is_rest()) {
			URL::redirect('/'.$this->name.'/'.$id);
		} elseif ($result) {
			Message::success('Record updated.');
			URL::redirect('/'.$this->name.'/detail/'.$id);
		} else {
			Message::info('No update for same record.');
			URL::redirect('/'.$this->name.'/edit/'.$id);
		}

		$data = array(
			'data' => $entry,
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

		// filter validation
		$filters = $this->filter_config();
		$fn = strtolower($this->request->method()).'_'.$this->request->uri->segments[2];
		$filter = (isset($filters[$fn])) ? $filters[$fn] : null;
		if ($filter) {

			$data = Request::data();
			$filter_o = Filter::instance($filter);
			$pass = $filter_o->valid($data);
			Request::set_data($data);

			if (!$pass) {
				Message::error($filter_o->errors->format());
				return array(
					'data' => $data,
				);
			}
		}

		return parent::execute($request);
	}
}