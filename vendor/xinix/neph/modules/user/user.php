<?php namespace Xinix\Neph\Modules\User;

use Neph\Core\DB\ORM\Model;

class User extends Model {
    var $transient = array(
        'full_name',
        'password2',
    );

    function columns() {
        $columns = parent::columns();
        $columns['password2'] = array(
            'filter' => 'required|match:password',
        );
        return $columns;
    }

    function get_full_name() {
        return trim($this->get('first_name').' '.$this->get('last_name'));
    }
}
