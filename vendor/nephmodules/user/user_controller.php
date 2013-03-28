<?php namespace Nephmodules\User;

use Nephmodules\Crud\Crud_Controller;
use \Neph\URL;

class User_Controller extends Crud_Controller {
	function get_index() {
		URL::redirect('/user/entries');
	}
}
