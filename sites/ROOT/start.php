<?php 

use \Neph\Core\Controller;
use \Neph\Core\Router;
use \Neph\Core\Response;
use \Neph\Core\Event;
use \Neph\Core\Console;

Controller::register('\\NephModules\\Module');
Controller::register('\\NephModules\\User');

Router::get('/hello', function() {
	$resp = new Response;
	$resp->view = '/xxx';
	$resp->data = array(
		'name' => 'budi',
	);
	return $resp;
});