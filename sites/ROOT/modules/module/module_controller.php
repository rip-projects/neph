<?php

use \Xinix\Neph\Crud\Crud_Controller;

class Module_Controller extends Crud_Controller {

    var $filters = array(
        'post_add' => array(
            'name:Name' => 'trim|required',
            'version' => 'trim|required',
        ),
        'post_edit' => array(
            'name' => 'trim|required',
            'version' => 'trim|required',
        ),
    );

    function crud_config() {
        $config = parent::crud_config();
        $config['columns'] = array('name', 'version');
        return $config;
    }

}