<?php

namespace Ludo\Support;

use DateTime;


/**
 * Class Validator
 *
 * @package Ludo\Support
 */
class Validator
{
    /**
     * Validate email data
     *
     * @param string $data raw data
     * @return bool
     */
    public static function email(string $data): bool
    {
        if (empty($data)) {
            return false;
        }

        return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate username
     *
     * @param string $data raw data
     * @return bool
     */
    public static function uname(string $data): bool
    {
        return !empty($data) && preg_match('/^[a-zA-Z0-9]{5,16}$/', $data);
    }

    /**
     * Validate password
     *
     * @param string $data raw data
     * @return bool
     */
    public static function password(string $data): bool
    {
        return !empty($data) && preg_match('/^[a-zA-Z0-9]{6,16}$/', $data);
    }

    /**
     * Validate if length of data is between the range( including the min and max value);
     *
     * @param string $data raw data
     * @param int $min minimum size
     * @param int $max maximum size
     * @return bool true if valid
     */
    public static function range(string $data, int $min, int $max): bool
    {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $min = intval($min);
        $max = intval($max);
        return $len >= $min && $len <= $max;
    }

    /**
     * Validate data length
     *
     * @param string $data raw data
     * @param int $length data length
     * @return bool
     */
    public static function len(string $data, int $length): bool
    {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $length = intval($length);
        return $len == $length;
    }

    /**
     * Validate data minimum size
     *
     * @param string $data raw data
     * @param int $min minimum size
     * @return bool
     */
    public static function minLength(string $data, int $min): bool
    {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $min = intval($min);
        return $len >= $min;
    }

    /**
     * Validate data maximum size
     *
     * @param string $data raw data
     * @param int $max maximum size
     * @return bool
     */
    public static function maxLength(string $data, int $max): bool
    {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $max = intval($max);
        return $len <= $max;
    }

    /**
     * Validate chinese word
     *
     * @param string $data raw data
     * @return bool
     */
    public static function chinese(string $data): bool
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $data);
    }

    /**
     * Validate postcode
     *
     * @param string $data raw data
     * @return bool
     */
    public static function postcode(string $data): bool
    {
        return !empty($data) && preg_match('/^\d{6}$/', $data);
    }

    /**
     * Whether data is an valid ip format
     *
     * @param string $data ip string
     * @return bool true for well formatted ip, vise versa.
     */
    public static function ip(string $data): bool
    {
        if (empty($data)) {
            return false;
        }

        return filter_var($data, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Whether data is an valid private ip
     * 10.0.0.0 through 10.255.255.255 (10/8)
     * 172.16.0.0 through 172.31.255.255 (172.16/12)
     * 192.168.0.0 through 192.168.255.255 (192.168/16)
     *
     * default the reserved range 169.254.0.0 through 169.254.255.255 will also include.
     *
     * @param string $data ip string
     * @param bool $includeReserved whether to include reserved ip range (169.254.0.0/16), default is true.
     * @return bool true for private ip, vise versa.
     */
    public static function privateIp(string $data, $includeReserved = true): bool
    {
        if (!self::ip($data)) {//kick non-ip off.
            return false;
        }

        if ($includeReserved) { //private ip + reserved ip.
            return !self::publicIp($data);
        } else { //just private ip. no reserved ip.
            return !self::publicIp($data, false);
        }
    }

    /**
     * Whether data is an valid public ip
     *
     * @param String $data ip string
     * @param bool $noReserved whether to exclude reserved ip range (169.254/16), default is true.
     * @return bool true for public ip, vise versa.
     */
    public static function publicIp(string $data, bool $noReserved = true): bool
    {
        if (!self::ip($data)) {//kick non-ip off.
            return false;
        }

        $flag = $noReserved ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : FILTER_FLAG_NO_PRIV_RANGE;
        return filter_var($data, FILTER_VALIDATE_IP, $flag) !== false;
    }

    /**
     * Whether data is a valid url
     *
     * @param string $data raw data
     * @param bool $schemeRequired whether require scheme
     * @param bool $hostRequired whether require host
     * @param bool $pathRequired whether require path
     * @param bool $queryStringRequired whether require query
     * @return bool
     */
    public static function url(string $data, bool $schemeRequired = false, bool $hostRequired = true, bool $pathRequired = false, bool $queryStringRequired = false): bool
    {
        if (empty($data)) {
            return false;
        }

        $url = parse_url($data);
        if ($schemeRequired && empty($url['scheme'])) {
            return false;
        }

        if ($hostRequired && empty($url['host'])) {
            return false;
        }

        $flags = 0;
        if ($pathRequired) $flags |= FILTER_FLAG_PATH_REQUIRED;
        if ($queryStringRequired) $flags |= FILTER_FLAG_QUERY_REQUIRED;

        return filter_var($data, FILTER_VALIDATE_URL, $flags) !== false;
    }

    /**
     * Whether data is a valid date
     *
     * @param string $data raw data
     * @return bool
     */
    public static function date(string $data): bool
    {
        if ($data instanceof DateTime) {
            return true;
        }

        if (strtotime($data) === false) {
            return false;
        }

        $date = date_parse($data);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Whether data is a valid mobile phone number
     *
     * @param string $data raw data
     * @return bool
     */
    public static function mobile(string $data): bool
    {
        if (empty($data)) {
            return false;
        }

        return (preg_match('/^1[0-9]{10}$/', $data, $match) !== 0);
    }
}
