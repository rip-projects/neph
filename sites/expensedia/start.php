<?php

use \Neph\Core\DB;

\Neph\Core\Loader::$aliases = array(
    'Controller' => '\\Neph\\Core\\Controller',
    'Router' => '\\Neph\\Core\\Router',
    'Config' => '\\Neph\\Core\\Config',
    'Console' => '\\Neph\\Core\\Console',
    'IoC' => '\\Neph\\Core\\IoC',
    'Session' => '\\Neph\\Core\\Session',
    'Model' => '\\Neph\\Core\\DB\\ORM\\Model',
);

Controller::register('user');
Controller::register('module');

if ( !is_cli() and Config::get('session.default', '') !== '') {
    Session::load();
}