<?php namespace NephModules\Module;

use \Neph\Console;
use \Neph\URL;
use \Neph\DB;

class Module_Controller extends \Nephmodules\Crud\Crud_Controller {

	function action_index() {
		URL::redirect('/module/entries');
	}

	function action_entries() {
		$a = DB::table('module')
			// ->where('id', '=', 1)
			->get();
		return array(
			'publish' => array(
				'entries' => $a,
			),
		);
	}

}