<?php namespace Xinix\Neph\Form;

use \Neph\Core\View;

class Form {
    public $id;
    public $meta = array();
    public $show_checkbox = true;
    public $show_tree = false;
    public $actions = array();

    public $template;

    function __construct($config = '') {
        if (!empty($config)) {
            foreach($config as $k => $v) {
                $this->$k = $v;
            }
        }

        $this->template = 'file://'.__DIR__.'/views/show.php';

        $this->id = uniqid('grid-');
    }

    function show($entry = array(), $readonly = false) {
        return View::instance($this->template)->render(array(
            'self' => $this,
            'entry' => $entry,
            'readonly' => $readonly,
            ));
    }

    function text($column, $value, $attrs = array()) {
        $formatter = get($this->meta, $column.'.format');
        if ($formatter) {
            return $formatter($column, $value, $attrs);
        }
        return '<span class="'.$attrs['class'].'">'.$value.'</span>';
    }

    function input($column, $value, $attrs = array()) {
        $formatter = get($this->meta, $column.'.format');
        if ($formatter) {
            return $formatter($column, $value, $attrs);
        }
        $method = 'input_'.get($this->meta, $column.'.type', 'string');
        if (!method_exists($this, $method)) {
            $method = 'input_string';
        }
        return $this->$method($column, $value, $attrs);
    }

    function input_password($column, $value, $attrs = array()) {
        return '<input type="password" name="'.$column.'" class="'.$attrs['class'].'" />';
    }

    function input_boolean($column, $value, $attrs = array()) {
        return '<select name="'.$column.'" class="'.$attrs['class'].'">
            <option value="" '.($value === '' ? 'selected' : '').'></option>
            <option value="0" '.($value === '0' ? 'selected' : '').'>False</option>
            <option value="1" '.($value === '1' ? 'selected' : '').'>True</option>
            </select>';
    }

    function input_integer($column, $value, $attrs = array()) {
        $extra = '';

        $meta_column = $this->meta[$column];
        if (isset($meta_column['source'])) {
            $extra .= 'data-provide="xtypeahead" ';
            if (is_string($meta_column['source'])) {
                $extra .= "data-source='".$meta_column['source']."' ";
            } else {
                $extra .= "data-source='".json_encode($meta_column['source'])."' ";
            }
            return '<input type="text" name="'.$column.'" value="'.$value.'" class="'.$attrs['class'].'" '.$extra.' data-minLength="0" />';
        } else {
            return '<input type="text" name="'.$column.'" value="'.$value.'" class="'.$attrs['class'].'" '.$extra.'/>';
        }

    }

    function input_string($column, $value, $attrs = array()) {
        return '<input type="text" name="'.$column.'" value="'.$value.'" class="'.$attrs['class'].'" />';
    }

    function input_text($column, $value, $attrs = array()) {
        return '<textarea name="'.$column.'" class="'.$attrs['class'].'">'.$value.'</textarea>';
    }

}