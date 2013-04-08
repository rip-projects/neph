<?php namespace Neph\Core\Session;

use \Neph\Core\String;
use \Neph\Core\Config;
use \Neph\Core\Cookie;
use \Neph\Core\Session;
use \Neph\Core\Session\Drivers\Driver;
use \Neph\Core\Session\Drivers\Sweeper;

class Payload {

	public $session;

	public $driver;

	public $exists = true;

	private $config = array();

	protected function expired() {
		return (time() - $this->session['last_activity']) > $this->config['lifetime'];
	}

	public function __construct(Driver $driver) {
		$this->driver = $driver;
		$default_config = array(
			'cookie' => 'SESSION',
			'lifetime' => 60 * 15,
			'path' => Config::get('config.base_path'),
			'domain' => $_SERVER['HTTP_HOST'],
			'secure' => false,
		);
		$config = Config::get('session');
		foreach ($default_config as $key => $value) {
			$this->config[$key] = (isset($config[$key])) ? $config[$key] : $default_config[$key];
		}
	}

	public function load($id) {
		if ( ! is_null($id)) $this->session = $this->driver->load($id);

		if (is_null($this->session) or $this->expired()) {
			$this->exists = false;

			$this->session = $this->driver->fresh();
		}

		if ( ! $this->has(Session::csrf_token)) {
			$this->put(Session::csrf_token, String::random(40));
		}
	}

	public function has($key) {
		return ( ! is_null($this->get($key)));
	}

	public function get($key, $default = null) {
		$session = $this->session['data'];

		if ( ! is_null($value = array_get($session, $key))) {
			return $value;
		} elseif ( ! is_null($value = array_get($session[':new:'], $key))) {
			return $value;
		} elseif ( ! is_null($value = array_get($session[':old:'], $key))) {
			return $value;
		}

		return value($default);
	}

	public function put($key, $value) {
		$this->session['data'][$key] = $value;
	}

	public function flash($key, $value) {
		$this->session['data'][':new:'][$key] = $value;
	}

	public function reflash() {
		$old = $this->session['data'][':old:'];

		$this->session['data'][':new:'] = array_merge($this->session['data'][':new:'], $old);
	}

	public function keep($keys) {
		foreach ((array) $keys as $key) {
			$this->flash($key, $this->get($key));
		}
	}

	public function forget($key) {
		unset($this->session['data'][$key]);
	}

	public function flush() {
		$token = $this->token();

		$session = array(Session::csrf_token => $token, ':new:' => array(), ':old:' => array());

		$this->session['data'] = $session;
	}

	public function regenerate() {
		$this->session['id'] = $this->driver->id();

		$this->exists = false;
	}

	public function token() {
		return $this->get(Session::csrf_token);
	}

	public function activity() {
		return $this->session['last_activity'];
	}

	public function save() {
		$this->session['last_activity'] = time();

		$this->age();

		$this->driver->save($this->session, $this->config, $this->exists);

		$this->cookie();

		if (method_exists($this->driver, 'sweep')) {
			$sweepage = $this->config['sweepage'];
			if (mt_rand(1, $sweepage[1]) <= $sweepage[0]) {
				$this->driver->sweep(time() - Config::get('session.lifetime'));
			}
		}
	}

	protected function age() {
		$this->session['data'][':old:'] = $this->session['data'][':new:'];

		$this->session['data'][':new:'] = array();
	}

	protected function cookie() {
		$minutes = (!empty($this->config['expire_on_close'])) ? $this->config['lifetime'] : 0;
		Cookie::put($this->config['cookie'], $this->session['id'], $minutes, $this->config['path'], $this->config['domain'], $this->config['secure']);
	}

}