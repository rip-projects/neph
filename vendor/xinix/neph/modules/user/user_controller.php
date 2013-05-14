<?php namespace Xinix\Neph\Modules\User;

use \Neph\Core\Controller;
use \Xinix\Neph\Crud\Crud_Controller;

class User_Controller extends Crud_Controller {

    public function grid_config() {
        $config = parent::grid_config();
        // \Console::log($config);
        $config['columns'] = array_diff($config['columns'], array('password', 'password2', 'first_name', 'last_name'));
        $config['columns'][] = 'full_name';
        return $config;
    }


    public function form_config() {
        $config = parent::form_config();
        $config['columns'] = array_diff($config['columns'], array('last_login'));
        $config['meta']['password2'] = array(
            'type' => 'password'
        );
        return $config;
    }

}