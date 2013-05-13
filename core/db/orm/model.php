<?php namespace Neph\Core\DB\ORM;

use \Neph\Core\DB;
use \Neph\Core\URL;
use \Neph\Core\IoC;
use \Neph\Core\Auth;
use \Neph\Core\Loader;
use \Neph\Core\Event;
use \Xinix\Neph\Filter\Filter;
use \Xinix\Neph\Message\Message;

class Model {
    public $connection = '';
    public $alias;

    protected $key = 'id';
    protected $name;
    protected $columns;
    protected $hidden = array();
    protected $transient = array();
    protected $system_keys = array(
        'parent'        => 'parent',
        'status'        => 'status',
        'created_time'  => 'created_time',
        'updated_time'  => 'updated_time',
        'created_by'  => 'created_by',
        'updated_by'  => 'updated_by',
    );
    protected $options = array();

    protected $attributes;
    protected $original;
    protected $exists = false;
    protected $filter;

    function __construct($attributes = array(), $options = '') {
        $this->options = (empty($options)) ? array() : (array) $options;

        if (isset($options['name'])) $this->name = $this->options['name'];
        if (isset($options['alias'])) $this->alias = $this->options['alias'];
        if (isset($options['key'])) $this->key = $this->options['key'];
        if (isset($options['exists'])) $this->exists = $this->options['exists'];

        if (!isset($this->name)) {
            $this->name = strtolower(class_basename($this));
        }

        if (!isset($this->alias)) {
            $this->alias = $this->name;
        }

        // after everything is ready, update the attributes
        if (!empty($attributes)) {
            $this->fill($attributes);
        } else {
            $this->attributes = $attributes;
        }
        $this->original = $this->attributes;
    }

    public function set($key, $value) {
        $value = $this->collection()->inflate($key, $value);
        if (method_exists($this, $method = 'set_'.$key)) {
            $this->$method($value);
        } else {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function get($key) {
        if (method_exists($this, $method = 'get_'.$key)) {
            return $this->$method();
        } else {
            return (isset($this->attributes[$key])) ? $this->attributes[$key] : null;
        }
    }

    public function collection() {
        return IoC::resolve('orm.manager')->collection($this->name);
    }

    public function children() {
        return $this->collection()->where($this->system_keys['parent'], '=', $this->attributes[$this->key])->get();
    }

    public function key($key_name = '') {
        return (empty($key_name)) ? $this->key : get($this->system_keys, $key_name);
    }

    public function columns() {
        return $this->collection()->columns();
    }

    public function hidden() {
        return $this->hidden;
    }

    protected function filter_config() {
        if (!isset($this->filter)) {
            $this->filter = array();
            foreach ($this->columns() as $key => $item) {
                $f = get($item, 'filter');
                if (empty($f)) continue;
                $this->filter[$key] = $f;
            }
        }
        return $this->filter;
    }

    public function valid() {
        $filter_o = new Filter($this->filter_config(), $this);
        $pass = $filter_o->valid($this->attributes);
        if (!$pass) {
            Message::error($filter_o->errors->format());
        }
        return $pass;
    }

    public function exists() {
        return $this->exists;
    }

    public function save() {
        $columns = $this->columns();
        $now = new \DateTime();
        $user_id = get(Auth::user(), 'id');

        if ($this->system_keys['status'] && isset($columns[$this->system_keys['status']])) {
            if (!isset($this->attributes[$this->system_keys['status']])) {
                $this->attributes[$this->system_keys['status']] = 1;
            }
        }

        if (!$this->exists) {
            if ($this->system_keys['created_time'] && isset($columns[$this->system_keys['created_time']])) {
                $this->attributes[$this->system_keys['created_time']] = $now;
            }
            if ($this->system_keys['created_by'] && isset($columns[$this->system_keys['created_by']])) {
                $this->attributes[$this->system_keys['created_by']] = $user_id;
            }
        }

        if ($this->system_keys['updated_time'] && isset($columns[$this->system_keys['updated_time']])) {
            $this->attributes[$this->system_keys['updated_time']] = $now;
        }
        if ($this->system_keys['updated_by'] && isset($columns[$this->system_keys['updated_by']])) {
            $this->attributes[$this->system_keys['updated_by']] = $user_id;
        }

        // \Console::log($this->attributes);
        // exit;

        // FIXME should we validate first of cleanup first?
        if (!$this->valid()) {
            return 0;
        }
        $this->attributes = $this->cleanup($this->attributes);

        if ($this->exists) {
            $result = $this->collection()
                ->where($this->key, '=', $this->attributes[$this->key])
                ->update($this->attributes);
        } else {
            $result = $this->attributes[$this->key] = $this->collection()
                ->insert_get_id($this->attributes);
        }
        $this->exists = true;

        if (empty($result)) return 0;
        else return 1;
    }

    function cleanup($attrs) {
        foreach ($attrs as $key => $attr) {
            if (in_array($key, $this->transient)) {
                unset($attrs[$key]);
                continue;
            }
        }
        return $attrs;
    }

    public function delete() {
        if ($this->exists) {
            $result = $this->collection()->where($this->key, '=', $this->attributes[$this->key])->delete();
            return $result;
        }
    }

    public function to_array() {
        $attr = array(
            '@type' => $this->name,
            '@url' => URL::site('/'.$this->name.'/'.$this->attributes[$this->key()].'.json'),
        );

        $columns = array_merge(array_keys($this->columns()), $this->transient);
        $columns = array_diff($columns, $this->hidden);

        foreach ($columns as $column) {
            $attr[$column] = $this->get($column);
        }
        return $attr;
    }

    public function fill($attributes) {
        foreach((array) $attributes as $key => $value) {
            $this->set($key, $value);
        }
    }
}
