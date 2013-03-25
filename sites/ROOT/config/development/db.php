<?php

return array(
	'default' => 'default',
	'connections' => array(
		'default' => array(
			'driver' 	=> 'mysql',
			'host' 		=> 'localhost',
			'database' 	=> 'neph_test',
			'username' 	=> 'root',
			'password' 	=> 'password',
		),
	),
	'drivers' => array(
		'mysql' => '\\Neph\\DB\\MySQL',
	),
	'fetch' => PDO::FETCH_CLASS,
	'profile' => false,
);