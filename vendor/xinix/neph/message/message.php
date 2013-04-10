<?php namespace Xinix\Neph\Message;

use \Neph\Core\View;
use \Neph\Core\Session;
use \Neph\Core\Event;

class Message {

    static $instance;
    static $session_id = 'message_messages';

    protected $dirty = false;
    protected $messages = array(
        'error' => array(),
        'success' => array(),
        'info' => array(),
        );

    function _show($severity = '') {
        $html = '';
        if (empty($severity)) {
            foreach ($this->messages as $key => $message) {
                $html .= $this->_show($key);
            }
        } else {
            foreach ($this->messages[$severity] as $k => $message) {
                $html .= View::instance('file://'.__DIR__.'/views/message/message.php')->render(array(
                   'self' => $this,
                   'message' => $message,
                   'severity' => $severity,
                ));
                unset($this->messages[$severity][$k]);
            }
        }
        return $html;
    }

    function _append($severity, $message) {
        $this->messages[$severity][] = $message;
        $this->dirty = true;
    }

    function __construct() {
        $messages = Session::get(static::$session_id);

        if (!empty($messages) && is_array($messages)) {
            $this->messages = $messages;
        }


        $m = $this;
        $sess_id = static::$session_id;
        Event::on('response.presend', function() use ($m, $sess_id) {
            Session::flash($sess_id, $m->messages);
        });
    }

    static function instance() {
        if (!static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    function __call($method, $parameters) {

        $append_methods = array('error', 'success', 'info');

        if (in_array($method, $append_methods)) {
            array_unshift($parameters, $method);
            return call_user_func_array(array($this, '_append'), $parameters);
        }

        return call_user_func_array(array($this, '_'.$method), $parameters);
    }

    static function __callStatic($method, $parameters) {
        return call_user_func_array(array(static::instance(), $method), $parameters);
    }
}