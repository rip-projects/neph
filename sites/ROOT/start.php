<?php 

use \Neph\Controller;
use \Neph\Router;
use \Neph\Response;

Controller::register('\\NephModules\\Module');

Router::get('/hello', function() {
	$resp = new Response;
	$resp->view = '/xxx';
	$resp->data = array(
		'name' => 'budi',
	);
	return $resp;
});