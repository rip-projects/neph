<?php namespace Neph\Core;

class Error {
	public static function exception($exception, $trace = true) {
		$response = Response::error(500, null, array('exception' => $exception));
		$response->render();
		$response->send();
		exit(1);
	}

	public static function native($code, $error, $file, $line) {
		if (error_reporting() === 0) return;

		$errorO = array(
			'message' => $error,
			'type' => $code,
			'file' => $file,
			'line' => $line,
			);
		if ( ! is_null($errorO)) {
			$response = Response::error(500, null, array('error' => $errorO));
			$response->render();
			$response->send();
			exit(1);
		}
	}

	public static function shutdown() {
		$error = error_get_last();

		if ( ! is_null($error)) {
			$response = Response::error(500, null, array('error' => $error));
			$response->render();
			$response->send();
			exit(1);
		}
	}
}