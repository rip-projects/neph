<?php namespace Neph;

class String {
	static function humanize($str, $separator = '_') {
		return ucwords(preg_replace('/['.$separator.']+/', ' ', strtolower(trim($str))));
	}
}