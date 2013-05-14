<?php namespace Xinix\Neph\CFieldModel;

use \Neph\Core\DB\ORM\Model;

class CFieldModel extends Model {
    public $custom = array();
    public $custom_table = 'cfield';

    protected $custom_attrs = array();

    public function get($key) {
        if (method_exists($this, $method = 'get_'.$key)) {
            return $this->$method();
        } elseif (in_array($key, $this->custom)) {
            if (isset($this->custom_attrs[$key])) {
                return $this->custom_attrs[$key];
            } else {
                return $this->custom_attrs[$key] = $this->fetch_custom($key);
            }
        } else {
            return (isset($this->attributes[$key])) ? $this->attributes[$key] : null;
        }
    }

    function cleanup() {
        foreach ($this->attributes as $key => $attr) {
            if (in_array($key, $this->transient)) {
                unset($this->attributes[$key]);
            } elseif (in_array($key, $this->custom)) {
                $this->custom_attrs[$key] = $this->attributes[$key];
                unset($this->attributes[$key]);
            }
        }
    }

    function _save() {
        $success = parent::_save();

        if ($success) {
            foreach($this->custom_attrs as $key => $value) {
                $this->save_custom($key, $value);
            }
        }

        return $success;
    }

    protected function save_custom($key, $value) {
        $field_row = array(
            'model' => $this->name,
            'row_id' => $this->identifier(),
            'skey' => $key,
            'svalue' => $value,
        );

        $old = $this->fetch_custom($key);
        if (isset($old)) {
            $this->collection()->connection()->table('cfield')
                ->where('model', '=', $this->name)
                ->where('row_id', '=', $this->identifier())
                ->where('skey', '=', $key)
                ->update($field_row);
        } else {
            $this->collection()->connection()->table('cfield')
                ->insert($field_row);
        }
    }

    protected function fetch_custom($key) {
        $row = $this->collection()->connection()->table($this->custom_table)
            ->where('model', '=', $this->name)
            ->where('row_id', '=', $this->identifier())
            ->where('skey', '=', $key)
            ->first();
        return ($row) ? get($row, 'svalue') : null;
    }
}