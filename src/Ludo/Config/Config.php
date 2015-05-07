<?php
namespace Ludo\Config;

class Config
{
    public static $config = array();

    public static function init()
    {
        if (empty(self::$config)) {
            $dir = SITE_ROOT.'/config/';
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $filename = $dir.$file;
                $config = require $filename;
                $basename = basename($filename, '.php');
                foreach ($config as $k => $v) {
                    self::$config[$basename.'.'.$k] = $v;
                }
            }
        }
        return self::$config;
    }

    public static function get($name)
    {
        $segments = explode('.', $name);
        if (count($segments) < 2) return null;
        $item = self::$config[$segments[0].'.'.$segments[1]];
        unset($segments[0], $segments[1]);
        $name = implode('.', $segments);
        empty($name) && $name = null;
        return array_get($item, $name);
    }

    public static function getConfig()
    {
        return self::$config;
    }
}