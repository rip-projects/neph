<?php namespace Neph\Core\Response;

use \Neph\Core\Response;

class Json extends Response {
    function render() {
        header('Content-Type: application/json');

        $publish = (empty($this->data['publish'])) ? null : $this->data['publish'];

        $this->content = to_json($publish);
        return $this->content;
    }
}
