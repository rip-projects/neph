<?php namespace Xinix\Neph\Crud;

use \Neph\Core\View;
use \Neph\Core\URL;

class Crud {

	var $id;
	var $columns = array();
	var $meta_columns = array();
	var $show_checkbox = true;
	var $excluded_columns = array('id', 'position', 'status', 'created_by', 'created_time', 'updated_by', 'updated_time');

	function __construct($config = '') {
		foreach($config as $k => $v) {
			$this->$k = $v;
		}

		$this->id = uniqid('grid-');
	}

	function grid($entries) {
		return View::instance('file://'.__DIR__.'/views/crud/grid.php')->render(array(
			'self' => $this,
			'entries' => $entries,
			));
	}

	function form() {
		return View::instance('file://'.__DIR__.'/views/crud/form.php')->render(array(
			'self' => $this,
			));
	}

	function input($column, $attrs = array()) {
		$meta_column = $this->meta_columns[$column];
		if ($column == 'password') {
			return '<input type="password" name="'.$column.'" class="'.$attrs['class'].'" />';
		}
		switch($meta_column['type']) {
		    case 'int':
		        return '<input type="text" name="'.$column.'" class="'.$attrs['class'].'" />';
		    case 'varchar':
		    case 'text':
		        return '<input type="text" name="'.$column.'" class="'.$attrs['class'].'" />';
		}
	}

	function breadcrumb($args) {
		$divider = '<span class="divider">/</span>';
		$html = '
		<ul class="breadcrumb">
	        <li><a href="'.URL::site('/').'">Home</a> '.$divider.'</li>
	    ';
	    $count = count($args);
	    $i = 1;
	    foreach ($args as $key => $value) {
	    	$html .= '<li><a href="'.URL::site($value).'">'.$key.'</a> '.(($i++ < $count) ? $divider : '').'</li>';
	    }
	    $html .= '</ul>';
	    return $html;
	}
}