<?php namespace Neph;

class URI {

	var $pathinfo;
	var $segments;
	var $extension;

	function __construct($pathinfo) {
		$this->pathinfo = $pathinfo;
		$this->segments = explode('/', $this->pathinfo);
		$last = count($this->segments) - 1;
		$exp = explode('.', $this->segments[$last]);
		$this->segments[$last] = $exp[0];
		$this->extension = (empty($exp[1])) ? '' : $exp[1];
	}

}