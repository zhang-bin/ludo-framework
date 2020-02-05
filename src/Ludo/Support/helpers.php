<?php

use Ludo\Support\Facades\Config;
use \Ludo\Foundation\Application;

/**
 * get the extension for a file
 *
 * @param String $fileName
 * @return string extension of a file. like: jpg or PNG or txt or php
 */
function ext($fileName)
{
    return substr(strrchr($fileName, '.'), 1);
}

/**
 * convert absolute location (eg. /usr/local/www/black_dog/uploads/1.html)
 * to relative path (which is relative to the site root,eg. /uploads/1.html)
 * @param string $path
 * @return string
 */
function abs2rel($path)
{
    return str_replace(SITE_ROOT, '', $path);
}

/**
 * convert relative path (which is relative to the site root,eg. /uploads/1.html)
 * to absolute location (eg. /usr/local/www/black_dog/uploads/1.html)
 * @param string $path
 * @return string
 */
function rel2abs($path)
{
    if ($path[0] != '/') {
        $path = '/' . $path;
    }

    return SITE_ROOT . $path;
}

/**
 * refine a size data
 *
 * @param string $size
 * @param int $fix
 * @return string
 */
function refineSize(string $size, int $fix = 2): string
{
    if ($size < 1024) {//<1K
        return round($size, $fix) . ' B';
    } elseif ($size < 1048576) {//<1M
        return round($size / 1024, $fix) . ' KB';
    } elseif ($size < 1073741824) {//<1G
        return round($size / 1048576, $fix) . ' MB';
    } else {
        return round($size / 1073741824, $fix) . ' GB';
    }
}

function debug($var, $print_r = true)
{
    echo '<pre>';
    $print_r ? print_r($var) : var_dump($var);
    echo '</pre>';
}

/**
 * Add an element to an array if it doesn't exist.
 *
 * @param array $array
 * @param string $key
 * @param mixed $value
 * @return array
 */
function array_add($array, $key, $value)
{
    if (!isset($array[$key])) $array[$key] = $value;
    return $array;
}

/**
 * Divide an array into two arrays. One with keys and the other with values.
 *
 * @param array $array
 * @return array
 */
function array_divide($array)
{
    return [array_keys($array), array_values($array)];
}

/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param array $array
 * @param string $prepend
 * @return array
 */
function array_dot($array, $prepend = '')
{
    $results = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $results = array_merge($results, array_dot($value, $prepend . $key . '.'));
        } else {
            $results[$prepend . $key] = $value;
        }
    }
    return $results;
}

/**
 * Get all of the given array except for a specified array of items.
 *
 * @param array $array
 * @param array $keys
 * @return array
 */
function array_except($array, $keys)
{
    return array_diff_key($array, array_flip((array)$keys));
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param array $array
 * @param Closure $callback
 * @param mixed $default
 * @return mixed
 */
function array_first($array, $callback, $default = null)
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $key, $value)) return $value;
    }

    return $default;
}

/**
 * Return the last element in an array passing a given truth test.
 *
 * @param array $array
 * @param Closure $callback
 * @param mixed $default
 * @return mixed
 */
function array_last($array, $callback, $default = null)
{
    return array_first(array_reverse($array), $callback, $default);
}

/**
 * Remove an array item from a given array using "dot" notation.
 *
 * @param array $array
 * @param string $key
 * @return void
 */
function array_forget(&$array, $key)
{
    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);
        if (!isset($array[$key]) || !is_array($array[$key])) {
            return;
        }
        $array =& $array[$key];
    }
    unset($array[array_shift($keys)]);
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param array $array
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }

    if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }
    return $array;
}

/**
 * Set an item to a given value using "dot" notation
 *
 * @param array $array
 * @param string $key
 * @param mixed $value
 * @return mixed|void
 */
function array_set(array &$array, string $key, $value)
{
    if (is_null($key)) {
        return;
    }

    $keys = explode('.', $key);
    while (count($keys) > 1) {
        $key = array_shift($keys);

        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }
        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;
}

/**
 * Get a value from the array, and remove it.
 *
 * @param array $array
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function array_pull(array &$array, string $key, $default = null)
{
    $value = array_get($array, $key, $default);
    array_forget($array, $key);
    return $value;
}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param string $haystack
 * @param string|array $needles
 * @return bool
 */
function end_with(string $haystack, $needles)
{
    foreach ((array)$needles as $needle) {
        if ($needle == substr($haystack, -strlen($needle))) {
            return true;
        }
    }
    return false;
}

/**
 * Determine if a given string starts with a given substring.
 *
 * @param string $haystack
 * @param string|array $needles
 * @return bool
 */
function start_with(string $haystack, $needles)
{
    foreach ((array)$needles as $needle) {
        if ($needle != '' && strpos($haystack, $needle) === 0) {
            return true;
        }
    }
    return false;
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param string $haystack
 * @param string|array $needles
 * @return bool
 */
function str_contains($haystack, $needles)
{
    foreach ((array)$needles as $needle) {
        if ($needle != '' && strpos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Replace a given value in the string sequentially with an array.
 *
 * @param string $search
 * @param array $replace
 * @param string $subject
 * @return string
 */
function str_replace_array($search, array $replace, $subject)
{
    foreach ($replace as $value) {
        $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
    }
    return $subject;
}

/**
 * Generate a "random" alpha-numeric string.
 *
 * Should not be considered sufficient for cryptography, etc.
 *
 * @param int $length
 * @return string
 * @throws Exception
 */
function str_random($length = 16)
{
    $pool = '3456789abcdefghjkmnpqrstuvwxyz';
    return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
}

/**
 * return the current page url, including http protocol, domain, port, and url, and query string.
 * e.g.: http://test.com/index.php?libk=yes
 *
 * @return string current page url
 */
function currentUrl(): string
{
    $schema = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';

    $port = ($_SERVER['SERVER_PORT'] != '80') ? ':' . $_SERVER['SERVER_PORT'] : '';
    return $schema . '://' . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
}