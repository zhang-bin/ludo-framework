<?php

/**
 * Get the extension for a file
 *
 * @param string $fileName file name
 * @return string extension of a file. like: jpg or PNG or txt or php
 */
function ext(string $fileName): string
{
    return substr(strrchr($fileName, '.'), 1);
}

/**
 * Convert absolute location (e.g. /usr/local/www/black_dog/uploads/1.html)
 * to relative path (which is relative to the site root,e.g. /uploads/1.html)
 *
 * @param string $path path name
 * @return string
 */
function abs2rel(string $path): string
{
    return str_replace(SITE_ROOT, '', $path);
}

/**
 * Convert relative path (which is relative to the site root,e.g. /uploads/1.html)
 * to absolute location (e.g. /usr/local/www/black_dog/uploads/1.html)
 *
 * @param string $path path name
 * @return string
 */
function rel2abs(string $path): string
{
    if ($path[0] != '/') {
        $path = '/' . $path;
    }

    return SITE_ROOT . $path;
}

/**
 * Refine a size data
 *
 * @param string $size raw size
 * @param int $fix precision
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

/**
 * Print pretty data
 *
 * @param mixed $var raw data
 * @param bool $print_r print or var dump
 */
function debug(mixed $var, bool $print_r = true): void
{
    echo '<pre>';
    $print_r ? print_r($var) : var_dump($var);
    echo '</pre>';
}

/**
 * Add an element to an array if it doesn't exist.
 *
 * @param array $array original array
 * @param string $key array key
 * @param mixed $value value
 * @return array
 */
function array_add(array $array, string $key, mixed $value): array
{
    if (!isset($array[$key])) $array[$key] = $value;
    return $array;
}

/**
 * Divide an array into two arrays. One with keys and the other with values.
 *
 * @param array $array original array
 * @return array
 */
function array_divide(array $array): array
{
    return [array_keys($array), array_values($array)];
}

/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param array $array original array
 * @param string $prepend prefix of key
 * @return array
 */
function array_dot(array $array, string $prepend = ''): array
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
 * Get all the given array except for a specified array of items.
 *
 * @param array $array original array
 * @param array $keys array keys
 * @return array
 */
function array_except(array $array, array $keys): array
{
    return array_diff_key($array, array_flip($keys));
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param array $array original array
 * @param Closure $callback array callback
 * @param mixed|null $default default value
 * @return mixed
 */
function array_first(array $array, Closure $callback, mixed $default = null): mixed
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $key, $value)) return $value;
    }

    return $default;
}

/**
 * Return the last element in an array passing a given truth test.
 *
 * @param array $array original array
 * @param Closure $callback array callback
 * @param mixed|null $default default value
 * @return mixed
 */
function array_last(array $array, Closure $callback, mixed $default = null): mixed
{
    return array_first(array_reverse($array), $callback, $default);
}

/**
 * Remove an array item from a given array using "dot" notation.
 *
 * @param array $array original array
 * @param string $key array key
 * @return void
 */
function array_forget(array &$array, string $key): void
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
 * @param array $array original array
 * @param ?string $key array key
 * @param mixed|null $default default value
 * @return mixed
 */
function array_get(array $array, ?string $key, mixed $default = null): mixed
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
 * @param array $array original array
 * @param ?string $key array key
 * @param mixed $value array value
 * @return void
 */
function array_set(array &$array, ?string $key, mixed $value): void
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
 * @param array $array original array
 * @param string $key array key
 * @param mixed|null $default default value
 * @return mixed
 */
function array_pull(array &$array, string $key, mixed $default = null): mixed
{
    $value = array_get($array, $key, $default);
    array_forget($array, $key);
    return $value;
}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param string $haystack haystack
 * @param array|string $needles needles
 * @return bool
 */
function end_with(string $haystack, array|string $needles): bool
{
    foreach ((array)$needles as $needle) {
        if (str_ends_with($haystack, $needle)) {
            return true;
        }
    }
    return false;
}

/**
 * Determine if a given string starts with a given substring.
 *
 * @param string $haystack haystack
 * @param array|string $needles needles
 * @return bool
 */
function start_with(string $haystack, array|string $needles): bool
{
    foreach ((array)$needles as $needle) {
        if ($needle != '' && str_starts_with($haystack, $needle)) {
            return true;
        }
    }
    return false;
}

/**
 * Replace a given value in the string sequentially with an array.
 *
 * @param string $search original string
 * @param array $replace replace from
 * @param string $subject replace to
 * @return string
 */
function str_replace_array(string $search, array $replace, string $subject): string
{
    foreach ($replace as $value) {
        $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
    }
    return $subject;
}

/**
 * Generate a "random" alphanumeric string.
 *
 * Should not be considered sufficient for cryptography, etc.
 *
 * @param int $length random length
 * @return string
 * @throws Exception
 */
function str_random(int $length = 16): string
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