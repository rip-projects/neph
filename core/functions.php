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

function l($message, $args = '') {
    return \Neph\Core\Lang::line(array('group' => 'messages', 'key' => $message, 'default' => $message), $args);
}

function value($value) {
    return (is_callable($value) and ! is_string($value)) ? call_user_func($value) : $value;
}

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * <code>
 *      // Set the $array['user']['name'] value on the array
 *      array_set($array, 'user.name', 'Taylor');
 *
 *      // Set the $array['user']['name']['first'] value on the array
 *      array_set($array, 'user.name.first', 'Michael');
 * </code>
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return void
 */
function array_set(&$array, $key, $value)
{
    if (is_null($key)) return $array = $value;

    $keys = explode('.', $key);

    // This loop allows us to dig down into the array to a dynamic depth by
    // setting the array value for each level that we dig into. Once there
    // is one key left, we can fall out of the loop and set the value as
    // we should be at the proper depth.
    while (count($keys) > 1)
    {
        $key = array_shift($keys);

        // If the key doesn't exist at this depth, we will just create an
        // empty array to hold the next value, allowing us to create the
        // arrays to hold the final value.
        if ( ! isset($array[$key]) or ! is_array($array[$key]))
        {
            $array[$key] = array();
        }

        $array =& $array[$key];
    }

    $array[array_shift($keys)] = $value;
}

/**
 * Remove an array item from a given array using "dot" notation.
 *
 * <code>
 *      // Remove the $array['user']['name'] item from the array
 *      array_forget($array, 'user.name');
 *
 *      // Remove the $array['user']['name']['first'] item from the array
 *      array_forget($array, 'user.name.first');
 * </code>
 *
 * @param  array   $array
 * @param  string  $key
 * @return void
 */
function array_forget(&$array, $key)
{
    $keys = explode('.', $key);

    // This loop functions very similarly to the loop in the "set" method.
    // We will iterate over the keys, setting the array value to the new
    // depth at each iteration. Once there is only one key left, we will
    // be at the proper depth in the array.
    while (count($keys) > 1)
    {
        $key = array_shift($keys);

        // Since this method is supposed to remove a value from the array,
        // if a value higher up in the chain doesn't exist, there is no
        // need to keep digging into the array, since it is impossible
        // for the final value to even exist.
        if ( ! isset($array[$key]) or ! is_array($array[$key]))
        {
            return;
        }

        $array =& $array[$key];
    }

    unset($array[array_shift($keys)]);
}

function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
  $merged = $array1;

  foreach ( $array2 as $key => &$value )
  {
    if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
    }
    else
    {
      $merged [$key] = $value;
    }
  }

  return $merged;
}

function with($object) {
    return $object;
}

function is_class_of($a, $b) {
    return $a == $b || is_subclass_of($a, $b);
}

function get($obj, $key, $def = '') {
    if (empty($obj)) return $def;

    $exploded = explode('.', $key, 2);
    if (is_object($obj)) {
        if (method_exists($obj, 'get')) {
            if ($key == 'children') {
                $v = $obj->children();
            } else {
                $v = $obj->get($exploded[0]);
            }
            if (empty($v)) return $def;
            return (empty($exploded[1])) ? $v : get($v, $exploded[1]);
        } else {
            if (empty($obj->{$exploded[0]})) return $def;
            return (empty($exploded[1])) ? $obj->{$exploded[0]} : get($obj->{$exploded[0]}, $exploded[1]);
        }
    } elseif (is_array($obj)) {
        if (empty($obj[$exploded[0]])) return $def;
        return (empty($exploded[1])) ? $obj[$exploded[0]] : get($obj[$exploded[0]], $exploded[1]);
    }
    return $def;
}

function to_json($obj) {
    if (is_object($obj) && method_exists($obj, 'to_array')) {
        return json_encode($obj->to_array(), JSON_PRETTY_PRINT);
    }

    return json_encode($obj, JSON_PRETTY_PRINT);
}