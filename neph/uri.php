<?php namespace Neph;

class URI {

	var $pathinfo;

	function __construct($pathinfo) {
		$this->pathinfo = $pathinfo;
		$this->segments = explode('/', $this->pathinfo);
	}

}