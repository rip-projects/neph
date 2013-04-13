<?php namespace Neph\Core\Response;

class Json extends \Neph\Core\Response {
    function render() {
        $publish = (empty($this->data['publish'])) ? null : $this->data['publish'];
        return json_encode($publish, JSON_PRETTY_PRINT);
    }
}
