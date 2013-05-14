<?php namespace Xinix\Neph\Filter;

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

    function get($key = ':all:') {
        if ($key == ':all:') {
            return $this->messages;
        }
        return (empty($this->messages[$key])) ? array() : $this->messages[$key];
    }

    function format() {
        $messages = $this->all();
        if (count($messages) > 0) {
            $result = '<ul class="error-list">';
            foreach ($messages as $message) {
                $result .= '<li>'.$message.'</li>';
            }
            $result .= '</ul>';
        } else {
            $result = '';
        }
        return $result;
    }

    function count() {
        return count($this->all());
    }
}