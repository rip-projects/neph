<?php namespace Xinix\Neph\Modules\User;

use \Xinix\Neph\CFieldModel\CFieldModel;

class User extends CFieldModel {
    public $transient = array(
        'full_name',
        'password2',
    );

    public $custom = array(
        'mail_notification',
    );

    function prepare_columns(&$columns) {
        parent::prepare_columns($columns);
        $password = $columns['password'];
        unset($columns['password']);
        $columns['password'] = $password;
        $columns['password2'] = array(
            'type' => 'password',
            'filter' => 'required|match:password',
        );
        $columns['mail_notification'] = array(
            'type' => 'boolean',
            'filter' => 'boolean',
        );

    }

    function get_full_name() {
        return trim($this->get('first_name').' '.$this->get('last_name'));
    }
}
