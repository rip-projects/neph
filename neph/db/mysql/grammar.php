<?php namespace Neph\DB\MySQL;

class Grammar extends \Neph\DB\Query\Grammar {

	/**
	 * The keyword identifier for the database system.
	 *
	 * @var string
	 */
	protected $wrapper = '`%s`';

}