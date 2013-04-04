<?php namespace Neph\Core;

class String {
    public static $encoding = null;

	static function humanize($str, $separator = '_') {
		return ucwords(preg_replace('/['.$separator.']+/', ' ', strtolower(trim($str))));
	}

    public static function random($length, $type = 'alnum') {
        return substr(str_shuffle(str_repeat(static::pool($type), 5)), 0, $length);
    }

    protected static function pool($type) {
        switch ($type) {
            case 'alpha':
                return 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            case 'alnum':
                return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            default:
                throw new \Exception("Invalid random string type [$type].");
        }
    }

    protected static function encoding() {
        return static::$encoding ?: static::$encoding = Config::get('config/encoding');
    }

    public static function length($value) {
        return (MB_STRING) ? mb_strlen($value, static::encoding()) : strlen($value);
    }
}