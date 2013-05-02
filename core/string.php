<?php namespace Neph\Core;

class String {
    public static $encoding = null;

	static function humanize($str, $separator = '_') {
		return ucwords(preg_replace('/['.$separator.']+/', ' ', strtolower(trim($str))));
	}

    public static function random($length, $type = 'alnum') {
        return substr(str_shuffle(str_repeat(static::pool($type), 5)), 0, $length);
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the URI starts with a given pattern
        // such as "library/*". This is only done when not root.
        if ($pattern !== '/')
        {
            $pattern = str_replace('*', '(.*)', $pattern).'\z';
        }
        else
        {
            $pattern = '^/$';
        }

        return preg_match('#'.$pattern.'#', $value);
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
        return static::$encoding ?: static::$encoding = Config::get('config.encoding');
    }

    public static function length($value) {
        return (MB_STRING) ? mb_strlen($value, static::encoding()) : strlen($value);
    }
}