<?php

use \Xinix\Neph\CMS\CMS;

\Neph\Core\Loader::$aliases = array(
    'Controller' => '\\Neph\\Core\\Controller',
    'Router' => '\\Neph\\Core\\Router',
    'Console' => '\\Neph\\Core\\Console',
    'IoC' => '\\Neph\\Core\\IoC',
    'Model' => '\\Neph\\Core\\DB\\ORM\\Model',
    'Event' => '\\Neph\\Core\\Event',
);

\Event::on('controller.load', function($data) {
    $c = CMS::find_controller_by_module_name($data['module']);
    return $c;
});