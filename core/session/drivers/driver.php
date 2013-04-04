<?php namespace Neph\Core\Session\Drivers;

use \Neph\Core\Config;
use \Neph\Core\String;

abstract class Driver {

	/**
	 * Load a session from storage by a given ID.
	 *
	 * If no session is found for the ID, null will be returned.
	 *
	 * @param  string  $id
	 * @return array
	 */
	abstract public function load($id);

	/**
	 * Save a given session to storage.
	 *
	 * @param  array  $session
	 * @param  array  $config
	 * @param  bool   $exists
	 * @return void
	 */
	abstract public function save($session, $config, $exists);

	/**
	 * Delete a session from storage by a given ID.
	 *
	 * @param  string  $id
	 * @return void
	 */
	abstract public function delete($id);

	/**
	 * Create a fresh session array with a unique ID.
	 *
	 * @return array
	 */
	public function fresh() {
		// We will simply generate an empty session payload array, using an ID
		// that is not currently assigned to any existing session within the
		// application and return it to the driver.
		return array('id' => $this->id(), 'data' => array(
			':new:' => array(),
			':old:' => array(),
		));
	}

	/**
	 * Get a new session ID that isn't assigned to any current session.
	 *
	 * @return string
	 */
	public function id() {
		do {
			$session = $this->load($id = String::random(40));
		} while (!is_null($session));

		return $id;
	}

}