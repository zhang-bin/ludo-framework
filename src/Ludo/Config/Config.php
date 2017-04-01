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
                if ($file[0] == '.' || $file[0] == '..') continue;
                $filename = $dir.$file;
                $ext = ext($filename);
                if ($ext != 'php') continue;
                $config = require $filename;
                $basename = basename($filename, '.php');
                foreach ($config as $k => $v) {
                    self::$config[$basename][$k] = $v;
                }
            }
        }
        return self::$config;
    }

    public static function get($name)
    {
        $segments = explode('.', $name);
        $length = count($segments);
        if ($length == 1) {
            return self::$config[$segments[0]];
        }
        $item = self::$config[$segments[0]];
        unset($segments[0]);

        $name = implode('.', $segments);
        empty($name) && $name = null;
        return array_get($item, $name);
    }

    public static function getConfig()
    {
        return self::$config;
    }
}