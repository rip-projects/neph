<?php namespace Neph\Core\Auth\Drivers;

use \Neph\Core\Router\Route;
use \Neph\Core\Config;
use \Neph\Core\Request;
use \Neph\Core\Response;
use \Neph\Core\IoC;
use \Xinix\Neph\Message\Message;

class Database extends Driver {
    protected function collection() {
        return IoC::resolve('orm.manager')->collection('user');
    }

    public function retrieve($id) {
        if (empty($id)) return null;
        try {
            return $this->collection()->find($id);
        } catch(\Exception $e) {
            throw new \Exception('Cannot retrieve user from collection, check if table exists');
        }
    }

    /**
     * Attempt to log a user into the application.
     *
     * @param  array $arguments
     * @return void
     */
    public function attempt($arguments = array()) {
        $result = $this->collection()
            ->where(function($query) use ($arguments) {
                $query->where('username', '=', $arguments['login'])
                    ->or_where('email', '=', $arguments['login']);
            })
            ->where('password', '=', $arguments['password'])->get();

        if (!empty($result)) {
            $user = $result[0]->to_array();
            return $this->login($user['id'], array_get($arguments, 'remember'));
        }
        return false;
    }

    public function authorized() {
        return false;
    }

    public function load() {

        Route::get('/logout', function() {
            $this->logout();
            return Response::redirect('/');
        });

        Route::any('/login', array(
            'view' => Config::get('auth.views.login'),
            'action' => function() {
                $data = Request::instance()->data();
                if ($data) {
                    if ($this->attempt($data)) {
                        return Response::redirect('/');
                    }
                    Message::error('Login failed');
                }
            },
        ));

    }
}