<?php namespace Xinix\Neph\Filter;

class FilterError extends \Exception {
    protected $parameters = array();

    static function instance($message, $parameters = '') {
        return new FilterError($message, $parameters);
    }

    function getParameters() {
        return $this->parameters;
    }

    function __construct($message, $parameters = '') {
        if (is_array($message)) {
            $parameters = $message;
            $message = '';
        }
        parent::__construct($message);
        $this->parameters = $parameters;
    }
}