<?php namespace Xinix\Neph\Filter;

use \Neph\Core\Request;
use \Neph\Core\Lang;

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

class FilterErrors {
    protected $messages = array();

    function add($key, $message) {
        if (!isset($this->messages[$key])) {
            $this->messages[$key] = array();
        }
        $this->messages[$key][] = $message;
    }

    function all($flatten = true) {
        if ($flatten) {
            $results = array();

            foreach($this->messages as $lines) {
                foreach($lines as $line) {
                    $results[] = $line;
                }
            }
            return $results;
        } else {
            return $this->messages;
        }
    }
}

class Filter {
    static $instance;
    protected $rules = array();
    public $errors;

    static function instance($rules = '') {
        if ($rules === '') return static::$instance;

        return static::$instance = new static($rules);
    }

    function __construct($rules) {
        foreach ($rules as $key => &$rule) {
            $rule = (is_string($rule)) ? explode('|', $rule) : $rule;
        }

        $this->rules = $rules;
    }

    function valid(&$attrs) {
        $this->errors = new FilterErrors();
        foreach ($this->rules as $key => $rules) {
            $this->check($key, $rules, $attrs);
        }
        return false;
    }

    function check($key, $rules, &$attrs) {
        $value = array_get($attrs, $key);
        $error = null;

        foreach ($rules as $rule) {
            list($rule, $parameters) = $this->parse($rule);
            try {
                $old_value = $value;
                $result = $this->{'filter_'.$rule}($key, $value, $attrs, $parameters, $this);
                if ($result instanceof \Exception) {
                    $error = $result;
                }
            } catch(\Exception $e) {
                $error = $e;
            }
            if ($value !== $old_value) array_set($attrs, $key, $value);

            if ($error) {
                $this->errors->add($key, $this->message($key, $rule, $error));
            }
        }
    }

    function message($key, $rule, $error) {
        $message = Lang::line(array('group' => 'filter', 'key' => $rule, 'default' => ''));
        $parameters = array();

        if ($error instanceof FilterError) {
            if ($error->getMessage()) {
                $message = $error->getMessage();
                $parameters = $error->getParameters();
            }
        } else {
            if (empty($message)) {
                $message = $error->getMessage();
            }
        }
        if (empty($message)) $message = 'Unknown label [filter.'.$rule.'] for column ['.$key.']';
        return l($message, $parameters);
    }

    function filter_trim($key, &$value) {
        $value = trim($value);
    }

    function filter_required($key, $value) {
        if (empty($value)) {
            throw FilterError::instance(array($key));
        }
    }

    protected function parse($rule) {
        $parameters = array();

        // The format for specifying validation rules and parameters follows a
        // {rule}:{parameters} formatting convention. For instance, the rule
        // "max:3" specifies that the value may only be 3 characters long.
        if (($colon = strpos($rule, ':')) !== false)
        {
            $parameters = str_getcsv(substr($rule, $colon + 1));
        }

        return array(is_numeric($colon) ? substr($rule, 0, $colon) : $rule, $parameters);
    }
}
