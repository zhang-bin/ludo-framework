<?php
namespace Ludo\Support;

class Validator {
    public static function email($data) {
        if (empty($data)) return false;
        return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
    }
    /**
     * validate if length of data is between the range( including the min and max value);
     *
     * @param String $data
     * @param int $min
     * @param int $max
     * @return bool true if valid
     */
    public static function range($data, $min, $max) {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $min = intval($min);
        $max = intval($max);
        return $len >= $min && $len <= $max;
    }

    public static function len($data, $length) {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $length = intval($length);
        return $len == $length;
    }

    public static function minLength($data, $min) {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $min = intval($min);
        return $len >= $min;
    }

    public static function maxLength($data, $max) {
        $len = mb_strlen($data, PROGRAM_CHARSET);
        $max = intval($max);
        return $len <= $max;
    }

    public static function chinese($data) {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $data);
    }

    public static function postcode($data) {
        return !empty($data) && preg_match('/^\d{6}$/', $data);
    }

    /**
     * whether data is an valid ip format
     * @param String $data ip string
     * @return bool true for well formatted ip, vise versa.
     */
    public static function ip($data) {
        if (empty($data)) return false;
        return filter_var($data, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * whether data is an valid private ip
     * 10.0.0.0 through 10.255.255.255 (10/8)
     * 172.16.0.0 through 172.31.255.255 (172.16/12)
     * 192.168.0.0 through 192.168.255.255 (192.168/16)
     *
     * default the reserved range 169.254.0.0 through 169.254.255.255 will also include.
     *
     * @param String $data ip string
     * @param bool $includeReserved whether to include reserved ip range (169.254.0.0/16), default is true.
     * @return bool true for private ip, vise versa.
     */
    public static function privateIp($data, $includeReserved=true) {
        if (!self::ip($data)) return false; //kick non-ip off.

        if ($includeReserved) { //private ip + reserved ip.
            return !self::publicIp($data);
        } else { //just private ip. no reserved ip.
            return !self::publicIp($data, false);
        }
    }

    /**
     * whether data is an valid public ip
     *
     * @param String $data ip string
     * @param bool $noReserved whether to exclude reserved ip range (169.254/16), default is true.
     * @return bool true for public ip, vise versa.
     */
    public static function publicIp($data, $noReserved=true) {
        if (!self::ip($data)) return false; //kick non-ip off.

        $flag = $noReserved ? FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE : FILTER_FLAG_NO_PRIV_RANGE;
        return filter_var($data, FILTER_VALIDATE_IP, $flag) !== false ? true : false;
    }

    public static function url($data, $schemeRequired=false, $hostRequired=true, $pathRequired=false, $queryStringRequired=flase) {
        if (empty($data)) return false;
        $flags = 0;
        if ($schemeRequired) $flags |= FILTER_FLAG_SCHEME_REQUIRED;
        if ($pathRequired) $flags |= FILTER_FLAG_PATH_REQUIRED;
        if ($queryStringRequired) $flags |= FILTER_FLAG_QUERY_REQUIRED;
        if ($hostRequired) $flags |= FILTER_FLAG_HOST_REQUIRED;

        return filter_var($data, FILTER_VALIDATE_URL, $flags) !== false;
    }

    /**
     * whether data is a valid date
     *
     * @param  string $data
     * @return bool
     */
    public static function date($data) {
        if ($data instanceof DateTime) return true;

        if (strtotime($data) === false) return false;

        $date = date_parse($data);

        return checkdate($date['month'], $date['day'], $date['year']);
    }
}