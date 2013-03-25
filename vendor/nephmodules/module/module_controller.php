<?php namespace NephModules\Module;

use \Neph\Console;

class Module_Controller extends \Nephmodules\Crud\Crud_Controller {
	function action_index() {
		return 'hello';
	}	
}