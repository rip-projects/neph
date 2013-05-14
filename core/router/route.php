<?php namespace Neph\Core\Router;

use \Neph\Core\Router;
use \Neph\Core\Config;
use \Neph\Core\Request;
use \Neph\Core\Response;
use \Neph\Core\Controller;
use \Neph\Core\Event;
use \Neph\Core\String;

class Route {
    public $view = '';
    protected $request;
    protected $action;

    protected $delegation;

    /**
     * Register a GET route with the router.
     *
     * @param  string|array  $route
     * @param  mixed         $action
     * @return void
     */
    public static function get($route, $action)
    {
        Router::instance()->register('GET', $route, $action);
    }

    /**
     * Register a POST route with the router.
     *
     * @param  string|array  $route
     * @param  mixed         $action
     * @return void
     */
    public static function post($route, $action)
    {
        Router::instance()->register('POST', $route, $action);
    }

    /**
     * Register a PUT route with the router.
     *
     * @param  string|array  $route
     * @param  mixed         $action
     * @return void
     */
    public static function put($route, $action)
    {
        Router::instance()->register('PUT', $route, $action);
    }

    public static function instance() {
        // forward to default route if non standard MVC accepted pathinfo
        if (Request::instance()->uri->pathinfo === '/') {
            Request::instance()->forward(Config::get('config.default_route', '/home'));
            return Router::instance()->route();
        } elseif (empty(Request::instance()->uri->segments[2])) {
            Request::instance()->forward('/'.Request::instance()->uri->segments[1].'/index');
            return Router::instance()->route();
        }

        return new static(Request::instance());
    }

    /**
     * Register a DELETE route with the router.
     *
     * @param  string|array  $route
     * @param  mixed         $action
     * @return void
     */
    public static function delete($route, $action)
    {
        Router::instance()->register('DELETE', $route, $action);
    }

    /**
     * Register a route that handles any request method.
     *
     * @param  string|array  $route
     * @param  mixed         $action
     * @return void
     */
    public static function any($route, $action) {
        Router::instance()->register('*', $route, $action);
    }

    public function __construct($request, $action = '') {
        $this->request = $request;

        if (is_array($action)) {
            foreach ($action as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->action = $action;
        }

        if ($action == '') {
            try {
                $controller = Controller::load(Request::instance()->uri->segments[1]);
            } catch(\Neph\Core\LoaderException $e) {
                $controller = null;
            } catch(\Exception $e) {
                throw $e;
            }

            $this->delegation = $controller;

            $view = Controller::get_resource_file('/views/'.Request::instance()->uri->segments[2].'.php');
            if (!empty($view)) {
                $this->view = '/'. $this->request->uri->segments[2];
            }
        }
    }

    function call() {
        $response = Event::until('route.pre_call');

        if (is_null($response)) {
            $response = $this->response();
        }

        $response = Response::instance($response);

        Event::emit('route.post_call', array(
            'response' => &$response,
        ));

        return $response;
    }

    function response() {
        if ($this->delegation) {

            $params = array_slice($this->request->uri->segments, 3);

            $action = strtolower($this->request->method()).'_'.$this->request->uri->segments[2];
            if (method_exists($this->delegation, $action)) {
                return call_user_func_array(array($this->delegation, $action), $params);
            }

            $action = 'any_'.$this->request->uri->segments[2];
            if (method_exists($this->delegation, $action)) {
                return call_user_func_array(array($this->delegation, $action), $params);
            }

            // get view file name just to check whether view is exist
            if (!empty($this->view)) {
                return '';
            }

        } else {
            if (is_callable($action = $this->action)) {
                return $action();
            }
        }

        // 404
        return Response::error(404);
    }
}