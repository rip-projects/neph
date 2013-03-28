<?php namespace Neph\Core\DB\MySQL;

class Grammar extends \Neph\Core\DB\Query\Grammar {

	/**
	 * The keyword identifier for the database system.
	 *
	 * @var string
	 */
	protected $wrapper = '`%s`';

	function columns($q) {

		return 'SHOW COLUMNS '.$this->select($q);
	}

}