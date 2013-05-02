<?php namespace Neph\Core\Helpers;

use \Neph\Core\URL;

class Page {
    public static function breadcrumb($args) {
        $divider = '<span class="divider">/</span>';
        $html = '
        <ul class="breadcrumb">
            <li><a href="'.URL::site('/').'">Home</a> '.$divider.'</li>
        ';
        $count = count($args);
        $i = 1;
        foreach ($args as $key => $value) {
            $html .= '<li><a href="'.URL::site($value).'">'.$key.'</a> '.(($i++ < $count) ? $divider : '').'</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}