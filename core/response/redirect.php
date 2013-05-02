<?php namespace Neph\Core\Response;

use \Neph\Core\Response;
use \Neph\Core\URL;

class Redirect extends Response {

    function __construct($url) {
        $this->url = $url;
    }

    function render() {
        header('Location: '.$this->url);
        return $this->content = '';
    }

    public function send() {
        // Dump all output buffering, this ensures
        // that symphony will send our redirect headers
        // properly if we've outputted any content from
        // within Laravel.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return parent::send();
    }

}
