<?php namespace Neph\Core\Response;

use \Neph\Core\Response;

class Json extends Response {
    function render() {
        header('Content-Type: application/json');

        $publish = (empty($this->data['publish'])) ? null : $this->data['publish'];
        if (is_a($publish, '\\Neph\\Core\\DB\\ORM\\Model')) {
            $this->content = json_encode($publish->to_array(), JSON_PRETTY_PRINT);
        } else {
            $this->content = json_encode($publish, JSON_PRETTY_PRINT);
        }
        return $this->content;
    }
}
