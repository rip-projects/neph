<?php namespace Xinix\Neph\Filter;

use \Neph\Core\Request;
use \Neph\Core\Lang;
use \Neph\Core\String;

class Filter {
    protected static $registries = array();

    protected $rules = array();
    protected $aliases = array();
    protected $context;

    public $errors;

    // static function instance($rules = '') {
    //     if ($rules === '') return static::$instance;

    //     return static::$instance = new static($rules);
    // }

    static function register($key, $fn) {
        static::$registries[$key] = $fn;
    }

    function __construct($rules, $context = '') {
        $this->context = $context;

        $new_rules = array();

        foreach ($rules as $key => $rule) {
            $alias = explode(':', $key);
            $key = $alias[0];
            $this->aliases[$key] = (empty($_alias[1])) ? String::humanize($key) : $_alias[1];

            $rule = (is_string($rule)) ? explode('|', $rule) : $rule;

            $new_rules[$key] = $rule;
        }
        $this->rules = $new_rules;
    }

    function valid(&$attrs = '') {
        $def = false;
        if ($attrs === '') {
            $def = true;
            $attrs = Request::instance()->data();
        }
        $this->errors = new FilterErrors();
        foreach ($this->rules as $key => $rules) {
            $this->check($key, $rules, $attrs);
        }
        if ($def) {
            Request::instance()->set_data($attrs);
        }
        return ($this->errors->count() == 0);
    }

    function check($key, $rules, &$attrs) {
        $value = array_get($attrs, $key);
        $error = null;

        foreach ($rules as $rule) {
            list($rule, $parameters) = $this->parse($rule);
            try {
                $done = false;
                $old_value = $value;
                if (isset(static::$registries[$rule])) {
                    $method = static::$registries[$rule];
                    $result = $method($key, $value, $attrs, $parameters, $this);
                    $done = true;
                }

                $method = 'filter_'.$rule;
                if (!$done && method_exists($this, $method)) {
                    $result = $this->$method($key, $value, $attrs, $parameters, $this);
                } else {
                    throw new \Exception('Filter ['.$rule.'] not found!');
                }
                if ($result instanceof \Exception) {
                    $error = $result;
                }
            } catch(\Exception $e) {
                $error = $e;
            }
            if ($value !== $old_value) array_set($attrs, $key, $value);

            if ($error) {
                $this->errors->add($key, $this->message($key, $rule, $error));
                return;
            }
        }
    }

    function message($key, $rule, $error) {
        $message = Lang::line(array('group' => 'filter', 'key' => $rule, 'default' => ''));

        $parameters = array();

        if ($error instanceof FilterError) {
            if ($error->getMessage()) {
                $message = $error->getMessage();
            }
            $parameters = $error->getParameters();
        } else {
            if (empty($message)) {
                $message = $error->getMessage();
            }
        }
        if (empty($message)) $message = 'Unknown label [filter.'.$rule.'] for column ['.$key.':'.$this->aliases[$key].']';

        return l($message, $parameters);
    }

    function filter_trim($key, &$value) {
        $value = trim($value);
    }

    function filter_unique($key, $value, $attrs, $parameters) {
        if ($this->context) {
            $q = $this->context->collection()
                ->where($key, '=', $value);

            if ($this->context->exists()) {
                $q->where('id', '!=', $this->context->get('id'));
            }

            $entries = $q->get();
            if (count($entries) > 0) {
                throw FilterError::instance(array($this->aliases[$key]));
            }
            return;
        }

        if (empty($parameters[0])) {
            throw new \Exception('Table name should be provided for unique filter');
        }

        throw new \Exception('No context for filter is not implemented yet');
    }

    function filter_max($key, $value, $attrs, $parameters) {
        if (strlen($value) > $parameters[0]) {
            throw FilterError::instance(array($this->aliases[$key], $parameters[0]));
        }
    }

    function filter_required($key, $value) {
        if (empty($value)) {
            throw FilterError::instance(array($this->aliases[$key]));
        }
    }

    function filter_normalize_empty($key, &$value) {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (empty($val)) unset($value[$key]);
            }
        }
    }

    function filter_integer($key, $value) {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) throw FilterError::instance(array($this->aliases[$key]));
    }

    function filter_datetime($key, $value) {
        if (!$value instanceof \DateTime) throw FilterError::instance(array($this->aliases[$key]));
    }

    function filter_match($key, $value, $attrs, $parameters) {
        if ($value != get($attrs, $parameters[0])) {
            throw FilterError::instance(array($this->aliases[$key], $this->aliases[$parameters[0]]));
        }
    }

    protected function parse($rule) {
        $parameters = array();

        // The format for specifying validation rules and parameters follows a
        // {rule}:{parameters} formatting convention. For instance, the rule
        // "max:3" specifies that the value may only be 3 characters long.
        if (($colon = strpos($rule, ':')) !== false) {
            $parameters = str_getcsv(substr($rule, $colon + 1));
        }

        return array(is_numeric($colon) ? substr($rule, 0, $colon) : $rule, $parameters);
    }
}
