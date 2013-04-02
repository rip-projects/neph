<?php

function full_url() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}

function is_cli() {
	return defined('STDIN') || (substr(PHP_SAPI, 0, 3) == 'cgi' && getenv('TERM'));
}

function starts_with($haystack, $needle) {
	return strpos($haystack, $needle) === 0;
}

function ends_with($haystack, $needle) {
	return $needle == substr($haystack, strlen($haystack) - strlen($needle));
}

function array_get($array, $key, $default = null)
{
    if (is_null($key)) return $array;

    // To retrieve the array item using dot syntax, we'll iterate through
    // each segment in the key and look for that value. If it exists, we
    // will return it, otherwise we will set the depth of the array and
    // look for the next segment.
    foreach (explode('.', $key) as $segment)
    {
        if ( ! is_array($array) or ! array_key_exists($segment, $array))
        {
            return value($default);
        }

        $array = $array[$segment];
    }

    return $array;
}

function class_basename($class)
{
    if (is_object($class)) $class = get_class($class);

    return basename(str_replace('\\', '/', $class));
}