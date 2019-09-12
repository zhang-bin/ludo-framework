<?php

use Ludo\Support\Facades\Config;
use \Ludo\Foundation\Application;

/**
 * get an pathInfo url from an innerUrl.
 * e.g. url('blog/add') will get http://SITE_URL/index.php/blog/add
 *
 * @param  String $innerUrl ==pathInfo
 * @return String right url with pathInfo.
 */
function url($innerUrl = '')
{
	return USING_MOD_REWRITE ? SITE_URL.'/'.$innerUrl : LD_PORTAL_URL.'/'.$innerUrl;
}

/**
 * aka. Root Url, get the root url from an inner url(based on SITE_URL).
 * e.g. rurl('img/util.js') will get http://SITE_URL/img/util.js
 *
 * @param String $innerUrl innerUrl which is based from SITE_URL
 * @return String root url
 */
function rurl($innerUrl)
{
	return SITE_URL.'/'.$innerUrl;
}

/**
 * get the absolute path for template file
 * e.g. tpl('user/login') will get /SITE_ROOT/app/templates/THEME/user/login.tpl
 *
 * @param string $tplPath tpl path related to tpl root
 * @return string
 */
function tpl($tplPath)
{
	return TPL_ROOT.'/'.$tplPath.php;
}

/**
 * redirect to an pathInfo url.
 * Note: you need to using this function with <b>return</b>
 * eg. return redirect('user/login');
 *
 * @param String $innerUrl ==pathInfo
 * @return void
 */
function redirect($innerUrl = '')
{
	if (!isAjax()) {
		header('location:'.url($innerUrl));
        if (Config::get('app.debug')) {
            Application::debug();
        }
        die;
	} else {
		echo json_encode(array(STATUS => GO, URL => url($innerUrl)));die;
	}
}

function redirectOut($outUrl)
{
	if (!isAjax()) {
		header('location:'.$outUrl);
        if (Config::get('app.debug')) {
            Application::debug();
        }
        die;
	} else {
		echo json_encode(array(STATUS => GO, URL => $outUrl));die;
	}
}

function isAjax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

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
	if ($path[0] != '/') $path = '/'.$path;
	return SITE_ROOT.$path;
}

/**
 * ceil the units of a integer which is bigger than units.
 *
 * @param int $digit
 * @return int
 */
function ceil10($digit)
{
	$str = strval(ceil($digit));
	$len = strlen($str);
	if ($str[$len-1] != 0) {
		$str[$len-1] = 0;
		$str[$len-2] = $str[$len-2] + 1;
	}
	return intval($str);
}

/**
 * return the current page url, including http protocol, domain, port, and url, and query string.
 * e.g.: http://test.com/index.php?libk=yes
 *
 * @return string current page url
 */
function currUrl()
{
	$url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://'.$_SERVER['HTTP_HOST'] : 'http://'.$_SERVER['HTTP_HOST'];

	if ($_SERVER['SERVER_PORT'] != '80')	$url .= ':'.$_SERVER["SERVER_PORT"]; //add port

	return $url.$_SERVER["REQUEST_URI"];
}

/**
 * get a pager html render
 *
 * @param Array $p
 * array(
 * 		'base'  => 'base url, like: product/list',
 * 		'cnt' => 'total items count',
 * 		'cur'   => 'current page id',
 * 		'size' => 'Optional, item count per page',
 * 		'span' => 'Optional, gap count between pager button',
 * )
 *
 * @return Array {
 * 		'start'=>'the start offset in queryLimit',
 * 		'rows'=>'rows to fetch in queryLimit',
 * 		'html'=>'page html render, e.g. 1  3 4 5 6  8'
 * }
 */
function pager(array $p)
{
	//==parse page variables
	if (empty($p['size'])) $p['size'] = PAGE_SIZE;
	if (empty($p['span'])) $p['span'] = PAGE_SPAN;

	//==if $p['base'] is not trailing with / or = (like user/list/ or user/list/?p=), 
	//add / to the end of base. eg. p[base] = user/list to user/list/. 
	$pBaseLastChar = substr($p['base'], -1);
	if ($pBaseLastChar != '/' && $pBaseLastChar != '=') $p['base'] .= '/';

	if ($p['cnt'] <= 0) {
		return array('start'=>0, 'rows'=>0, 'html'=>'');
	}

	if (($p['cnt'] % $p['size']) == 0) {
		$p['total'] = $p['cnt'] / $p['size'];
	} else {
		$p['total'] = floor($p['cnt'] / $p['size']) + 1;
	}
	//if only have one page don't show the pager
	if ($p['total'] == 1) return array('start'=>0, 'rows'=>0, 'html'=>'');

	if (isset($p['cur'])) {
		$p['cur'] = intval($p['cur']);
	} else {
		$p['cur'] = 1;
	}
	if ($p['cur'] < 1) {
		$p['cur'] = 1;
	}
	if ($p['cur'] > $p['total']) {
		$p['cur'] = $p['total'];
	}

	if ($p['total'] <= $p['span']+1) {
		$p['start'] = 1;
		$p['end'] = $p['total'];
	} else {
		if ($p['cur'] < $p['span']+1) {
			$p['start'] = 1;
			$p['end'] = $p['start'] + $p['span'];
		} else {
			$p['start'] = $p['cur'] - $p['span'] + 1;
			if ($p['start'] > $p['total']-$p['span']) $p['start'] = $p['total'] - $p['span'];
			$p['end'] = $p['start'] + $p['span'];
		}
	}
	if ($p['start'] < 1) $p['start'] = 1;
	if ($p['end'] > $p['total']) $p['end'] = $p['total'];


	$p['offset'] = ($p['cur'] - 1) * $p['size'];


	//==render with html
	$html = '';
	if ($p['start'] != 1) {
		$html .='<a href="'. url($p['base'].'1') .'" class="p">1</a>';
		if ($p['start'] - 1 > 1) $html .='&bull;&bull;';
	}
	for ($i = $p['start']; $i <= $p['end']; $i++) {
		if ($p['cur'] == $i) {
			$html .='<strong class="p_cur">' . $i . '</strong>';
		} else {
			$html .='<a href="'. url($p['base'].$i) .'" class="p">' . $i . '</a>';
		}
	}
	if ($p['end'] != $p['total']) {
		if ($p['total'] - $p['end'] > 1) $html .='&bull;&bull;';
		$html .= '<a href="'. url($p['base'].$p['total']) .'" class="p">' . $p['total'] . '</a>';
	}
	$html .= '<strong class="p_info">' . $p['cnt'] . '&nbsp'.'total items | ' . $p['size'] .'&nbsp'.'items each page</strong>';

	return array('start'=>$p['offset'], 'rows'=>$p['size'], 'html'=>$html, '');
}

/**
 * return the right new line of the web server:
 * Unix: \n
 * Win: \r\n
 * Mac: \r
 *
 */
function nl()
{
	return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\r\n" : "\n";
}

/**
 * return current user's real IP. It can get IP behind Proxy.
 */
function realIp()
{
	static $realIp = '';

	if (!$realIp) {
		$cip = getenv('HTTP_CLIENT_IP');
		$xip = getenv('HTTP_X_FORWARDED_FOR');
		$rip = getenv('REMOTE_ADDR');
		$srip = $_SERVER['REMOTE_ADDR'];
		if($cip && strcasecmp($cip, 'unknown')) {
			$realIp = $cip;
		} elseif($xip && strcasecmp($xip, 'unknown')) {
			$realIp = $xip;
		} elseif($rip && strcasecmp($rip, 'unknown')) {
			$realIp = $rip;
		} elseif($srip && strcasecmp($srip, 'unknown')) {
			$realIp = $srip;
		}
		$match = array();
		preg_match('/[\d\.]{7,15}/', $realIp, $match);
		$realIp = $match[0] ? $match[0] : '0.0.0.0';
	}
	return $realIp;
}

/**
 * refine a size data
 *
 * @param string $size
 * @param int $fix
 * @return string
 */
function refineSize($size, $fix = 2)
{
	if ($size < 1024)	return round($size, $fix).' B'; //<1K
	elseif ($size < 1048576) return round($size / 1024, $fix).' KB'; //<1M
	elseif ($size < 1073741824)	return round($size / 1048576, $fix).' MB'; //<1G
	else return round($size / 1073741824, $fix).' GB';
}

function addSuffix($FileName, $Suffix)
{
	$ext = strrchr($FileName, '.');

	if (!$ext)
		return $FileName.$Suffix;

	return substr($FileName, 0, strpos($FileName, '.')).$Suffix.$ext;
}

function debug($var, $print_r=true)
{
	echo '<pre>';
	$print_r ? print_r($var) : var_dump($var);
	echo '</pre>';
}

function logined()
{
	return !empty($_SESSION[USER]);
}

/**
 * redirect to login page with jurl
 *
 * @param string $jurl
 * @param bool $isOuterJurl
 * @return string
 */
function gotoLogin($jurl = '', $isOuterJurl = false)
{
	if (empty($jurl)) {
		$jurl = currUrl();
		$isOuterJurl = true;
	}
	$jurl = $isOuterJurl ? '?jurl='.urlencode($jurl) : '?jurl='.urlencode(url($jurl));
	redirect('user/'.$jurl);
}

function logout()
{
	unset($_SESSION);
	session_destroy();
	redirect();
}

/**
 * Add an element to an array if it doesn't exist.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
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
	return array(array_keys($array), array_values($array));
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
	$results = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$results = array_merge($results, array_dot($value, $prepend.$key.'.'));
		} else {
			$results[$prepend.$key] = $value;
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
 * @param  array    $array
 * @param  Closure  $callback
 * @param  mixed    $default
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
 * @param  array    $array
 * @param  Closure  $callback
 * @param  mixed    $default
 * @return mixed
 */
function array_last($array, $callback, $default = null)
{
	return array_first(array_reverse($array), $callback, $default);
}

/**
 * Remove an array item from a given array using "dot" notation.
 *
 * @param  array   $array
 * @param  string  $key
 * @return void
 */
function array_forget(&$array, $key)
{
	$keys = explode('.', $key);

	while (count($keys) > 1) {
		$key = array_shift($keys);
		if ( ! isset($array[$key]) || ! is_array($array[$key])) {
			return;
		}
		$array =& $array[$key];
	}
	unset($array[array_shift($keys)]);
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
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
 * @return mixed
 */
function array_set(array &$array, string $key, $value)
{
    if (is_null($key)) {
        return $array;
    }

    $keys = explode('.', $key);
    while(count($keys) > 1) {
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
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function array_pull(&$array, $key, $default = null)
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
function end_with($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ($needle == substr($haystack, -strlen($needle))) {
			return true;
		}
	}
	return false;
}

/**
 * Determine if a given string starts with a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function start_with($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ($needle != '' && strpos($haystack, $needle) === 0) {
			return true;
		}
	}
	return false;
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param  string        $haystack
 * @param  string|array  $needles
 * @return bool
 */
function str_contains($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ($needle != '' && strpos($haystack, $needle) !== false) {
			return true;
		}
	}
	return false;
}

/**
 * Replace a given value in the string sequentially with an array.
 *
 * @param  string  $search
 * @param  array  $replace
 * @param  string  $subject
 * @return string
 */
function str_replace_array($search, array $replace, $subject)
{
	foreach ($replace as $value) {
		$subject = preg_replace('/'.$search.'/', $value, $subject, 1);
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

function csrf_token()
{
    if (empty($_SESSION[USER]['token'])) {
        $token = str_random(32);
        $_SESSION[USER]['token'] = $token;
    }
    return $_SESSION[USER]['token'];
}

function csrf_token_validate($token)
{
    return $_SESSION[USER]['token'] == trim($token);
}

function csrf_token_refresh() {
    $token = str_random(32);
    $_SESSION[USER]['token'] = $token;
    return $token;
}
