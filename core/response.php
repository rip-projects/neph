<?php namespace Neph\Core;

class Response {
    static public $instance;

    static $STATUS = array(
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );

    var $status = 200;
    var $content;
    var $uri;
    var $layout;
    var $view = '';
    var $data;
    var $pre_data = '';
    var $post_data = '';

    static function error($status = 500, $message = '', $data = '') {
        return new \Neph\Core\Response\Error($status, $message, $data);
    }

    static function instance($response) {
        $event_response = Event::until('response.instance', array(
            'response' => $response
            ));

        if ($event_response instanceof Response) {
            return $event_response;
        }

        if ($response instanceof Response) {
            return $response;
        }

        if (Request::instance()->is_rest()) {
            return new \Neph\Core\Response\Json($response);
        }

        return new static($response);
    }

    function __construct($data = '') {
        if (is_string($data)) {
            $this->pre_data = $data;
        } else {
            $this->data = (array) $data;
        }
        $this->uri = Request::instance()->uri;
        $this->layout = Config::get('config.layout');
    }

    function view($view) {
        $this->view = $view;
        return $this;
    }

    function render() {
        $this->data['_response'] = $this;

        Event::emit('response.pre_render', array(
            'response' => $this
            ));

        if (empty($this->view)) {
            $this->view = '/'. (isset($this->uri->segments[2]) ? $this->uri->segments[2] : 'index');
        }

        $this->pre_data = ob_get_clean().$this->pre_data;

        $this->content = Event::until('response.render', array(
            'response' => &$this,
            ));

        if (Controller::get_resource_file('/views'.$this->view.'.php')) {
            $view = View::instance($this->view)
                ->prepend($this->pre_data)
                ->append($this->post_data);

            if ($this->layout) {
                $view->layout($this->layout);
            }
            $this->content = $view->render($this->data);
        } else {
            $this->content = $this->pre_data;
        }

        return $this->content;
    }

    function rendered() {
        return $this->content;
    }

    function send_headers() {
        if (!is_cli()) {
            if (empty($this->status) || $this->status != 200) {
                $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
                $error = static::$STATUS[$this->status];
                if (strpos(php_sapi_name(), 'cgi') === 0) {
                    header('Status: '.$this->status.' '.$error, TRUE);
                } else {
                    header(($server_protocol ? $server_protocol : 'HTTP/1.1').' '.$this->status.' '.$error, TRUE, $this->status);
                }
            }

            foreach (Cookie::$jar as $cookie) {
                setcookie($cookie['name'], $cookie['value'], $cookie['expiration'], $cookie['path'], $cookie['domain']);
            }
        }
    }

    function send() {
        $this->send_headers();
        echo $this->rendered();
    }
}
