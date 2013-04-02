<?php

return array(
	'default' => 'default',
	'connections' => array(
		'default' => array(
			'driver' 	=> 'mysql',
			'host' 		=> 'localhost',
			'database' 	=> 'neph_cm',
			'username' 	=> 'root',
			'password' 	=> 'password',
		),
	),
	// 'drivers' => array(
	// 	'mysql' => '\\Neph\\Core\\DB\\MySQL',
	// ),
	'fetch' => PDO::FETCH_CLASS,
	'profile' => false,
);