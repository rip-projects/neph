<?php namespace Xinix\Neph\Form;

use \Neph\Core\View;

class Form {
    public $id;
    public $meta = array();
    public $show_checkbox = true;
    public $show_tree = false;
    public $actions = array();

    function __construct($config = '') {
        if (!empty($config)) {
            foreach($config as $k => $v) {
                $this->$k = $v;
            }
        }

        $this->id = uniqid('grid-');
    }

    function show($entry = array(), $readonly = false) {
        return View::instance('file://'.__DIR__.'/views/show.php')->render(array(
            'self' => $this,
            'entry' => (is_a($entry, '\\Neph\\Core\\DB\\ORM\\Model')) ? $entry->to_array() : (array) $entry,
            'readonly' => $readonly,
            ));
    }

    function text($column, $value, $attrs = array()) {
        return '<span class="'.$attrs['class'].'">'.$value.'</span>';
    }

    function input($column, $value, $attrs = array()) {
        $meta_column = $this->meta[$column];

        if ($column == 'password') {
            return $this->input_password($column, $value, $attrs);
        }
        switch($meta_column['type']) {
            case 'integer':
                return $this->input_integer($column, $value, $attrs);
            case 'string':
                return $this->input_string($column, $value, $attrs);
            case 'text':
                return $this->input_text($column, $value, $attrs);
        }

        return $this->input_string($column, $value, $attrs);
    }

    function input_password($column, $value, $attrs = array()) {
        return '<input type="password" name="'.$column.'" value="'.$value.'" class="'.$attrs['class'].'" />';
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