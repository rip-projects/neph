<?php namespace Xinix\Neph\Crud;

use \Neph\Core\Response;
use \Neph\Core\IoC;
use \Neph\Core\Router\Route;
use \Neph\Core\DB;
use \Neph\Core\DB\ORM\Model;
use \Neph\Core\Event;
use \Neph\Core\Controller;
use \Neph\Core\Request;
use \Xinix\Neph\Filter\Filter;
use \Xinix\Neph\Message\Message;
use \Xinix\Neph\Grid\Grid;
use \Xinix\Neph\Form\Form;


class Crud_Controller extends Controller {

    protected $hidden = array(
        'id',
        'x_position',
        'x_status',
        'x_created_by',
        'x_created_time',
        'x_updated_by',
        'x_updated_time'
    );

    protected $name;
    protected $collection;
    protected $grid_config;
    protected $form_config;
    protected $filters = array();

    public function __construct() {
        parent::__construct();

        if (empty($this->name)) {
            $name = explode('_', class_basename($this));
            $this->name = strtolower($name[0]);
            if ($this->name === 'crud') {
                $this->name = Request::instance()->uri->segments[1];
            }
        }

        if (DB::check($this->name)) {
            $this->collection = IoC::resolve('orm.manager')->collection($this->name);
        }

        $self = $this;

        Event::on('route.pre_call', function() use ($self) {
            if (Request::instance()->is_rest()) {
                if (is_numeric(Request::instance()->uri->segments[2])) {
                    return $self->any_index(intval(Request::instance()->uri->segments[2]));
                } else {
                    return $self->any_index();
                }
            }

            // filter validation
            $filters = $self->filter_config();
            if (!empty($filters)) {
                $fn = strtolower(Request::instance()->method()).'_'.Request::instance()->uri->segments[2];
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
            }
        });
    }

    protected function filter_config() {
        return $this->filters;
    }

    protected function grid_config() {
        if (!isset($this->grid_config)) {
            $meta = $this->collection->columns();
            $this->grid_config = array(
                'columns' => array_diff(array_keys($meta), $this->hidden),
                'meta' => $meta,
                'actions' => array(
                    'edit' => '/'.$this->name.'/edit',
                    'delete' => '/'.$this->name.'/delete',
                ),
            );
        }

        return $this->grid_config;
    }

    protected function form_config() {
        if (!isset($this->form_config)) {
            $meta = $this->collection->columns();
            $this->form_config = array(
                'columns' => array_diff(array_keys($meta), $this->hidden),
                'meta' => $meta,
                'actions' => array(
                    'edit' => '/'.$this->name.'/edit',
                    'delete' => '/'.$this->name.'/delete',
                ),
            );
        }
        return $this->form_config;
    }

    public function any_index($id = 0) {
        if (!Request::instance()->is_rest()) {
            return Response::redirect('/'.$this->name.'/entries');
        }

        $method = Request::instance()->method();

        switch ($method) {
            case 'GET':
                $fn = ($id) ? 'entry' : 'entries';
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
            if (method_exists($this, $fn = strtolower($method).'_'.$fn)) {
                return $this->$fn($id);
            } else {
                $fn = 'action_'.$fn;
                return $this->$fn($id);
            }
        }
    }

    public function get_entry($id) {
        return array(
            'publish' => $this->collection->find($id),
            'form' => new Form($this->form_config()),
        );
    }

    public function get_add() {
        $data = array(
            'form' => new Form($this->form_config()),
        );
        return $data;
    }

    public function get_edit($id) {
        $data = array(
            'data' => $this->collection->find($id),
            'form' => new Form($this->form_config()),
        );
        return $data;
    }

    public function get_delete($id) {
        $ids = explode(',', $id);
        foreach ($ids as $id) {
            $id = trim($id);
            if (empty($id)) continue;

            $row = $this->collection->find($id);
            if (!empty($row)) {
                $row->delete();
            }
        }

        if (Request::instance()->is_rest()) {
            return true;
        } else {
            Message::success('Record deleted.');
            return Response::redirect('/'.$this->name.'/entries');
        }
    }

    public function get_entries() {
        $data = array();

        $config = $this->grid_config();
        if (!Request::instance()->is_rest() && !empty($config['show_tree'])) {
            $data['publish']['entries'] = $this->collection->root();
        } else {
            $results = $this->collection->filter_query($_GET)->get();
            $entries = array();
            foreach ($results as $key => $row) {
                $entries[] = $row->to_array();
            }
            $data['publish']['entries'] = $entries;
        }

        $data['grid'] = new Grid($this->grid_config());
        return $data;
    }

    public function post_add() {

        $entry = $this->collection->prototype(Request::instance()->data());
        $entry->save();

        Message::success('Record added.');

        if (Request::instance()->is_rest()) {
            return Response::redirect('/'.$this->name.'/'.$entry->get('id'));
        } else {
            return Response::redirect('/'.$this->name.'/entry/'.$entry->get('id'));
        }
    }

    public function post_edit($id) {

        $entry = Request::instance()->data();
        unset($entry['id']);

        $entry_o = $this->collection->find($id);
        $entry_o->fill($entry);
        $result = $entry_o->save();

        Message::success(($result) ? 'Record updated.' : 'No update for same record.');

        if (Request::instance()->is_rest()) {
            return Response::redirect('/'.$this->name.'/'.$id);
        } elseif ($result) {
            return Response::redirect('/'.$this->name.'/entry/'.$id);
        } else {
            return Response::redirect('/'.$this->name.'/edit/'.$id);
        }
    }
}