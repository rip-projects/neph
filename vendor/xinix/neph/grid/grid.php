<?php namespace Xinix\Neph\Grid;

use \Neph\Core\View;

class Grid {
    public $id;
    public $columns = array();
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
        $formatter = (isset($this->meta[$key]['format'])) ? $this->meta[$key]['format'] : null;
        if (isset($formatter)) {
            if (is_callable($formatter)) {
                return $formatter($value, $key, $entry);
            } elseif (function_exists($formatter)) {
                return $formatter($value, $key, $entry);
            }
        }
        return $value;
    }
}