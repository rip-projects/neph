<?php namespace Neph\Core\Router;

use \Neph\Core\Router;
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
    public static function any($route, $action)
    {
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

        if ($this->action === '') {
            // forward to default route if non standard MVC accepted pathinfo
            if ($this->request->uri->pathinfo === '/') {
                Request::instance()->forward('/home/index');
            } elseif (empty($this->request->uri->segments[2])) {
                Request::instance()->forward('/'.$this->request->uri->segments[1].'/index');
            }

            try {
                $controller = Controller::load($this->request->uri->segments[1]);
            } catch(\Neph\Core\LoaderException $e) {
                $controller = null;
            } catch(\Exception $e) {
                return Response::error(500, $e->getMessage(), array('exception' => $e));
            }
            $this->delegation = $controller;
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

            $view = Controller::get_resource_file('/views/'.$this->request->uri->segments[2].'.php');
            if (!empty($view)) {
                $this->view = '/'. $this->request->uri->segments[2];
            }
            // Controller::get_resource_file('/views'.$this->view.'.php')

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