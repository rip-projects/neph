<?php 

use \Neph\Controller;
use \Neph\Router;
use \Neph\Response;
use \Neph\Event;
use \Neph\Console;

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