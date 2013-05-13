<?php namespace Xinix\Neph\Grid;

use \Neph\Core\View;

class Grid {
    public $id;
    public $columns = array();
    public $meta = array();
    public $show_checkbox = true;
    public $show_tree = false;
    public $actions = array();

    protected $registries = array();

    function __construct($config = '') {
        if (!empty($config)) {
            foreach($config as $k => $v) {
                $this->$k = $v;
            }
        }

        $this->id = uniqid('crud-');
    }

    function show($entries) {
        return View::instance('file://'.__DIR__.'/views/show.php')->render(array(
            'self' => $this,
            'entries' => $entries,
            ));
    }

    function row($entry, $level = 0) {
        $d = array(
            'self' => $this,
            'level' => $level,
            'entry' => $entry,
            );
        return View::instance('file://'.__DIR__.'/views/row.php')->render($d);
    }

    function format($value, $key, $entry) {
        if (method_exists($this, 'format_'.get($this->meta, $key.'.type'))) {
            $formatter = array($this, 'format_'.get($this->meta, $key.'.type'));
        } else {
            $formatter = get($this->meta, $key.'.format');
        }

        if (isset($formatter)) {
            if (is_callable($formatter)) {
                return $formatter($value, $key, $entry);
            } elseif (function_exists($formatter)) {
                return call_user_func_array($formatter, array($value, $key, $entry));
            }
        }
        return $value;
    }

    function format_decimal($value) {
        return number_format(doubleval($value));
    }

}