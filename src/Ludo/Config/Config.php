<?php
namespace Ludo\Config;

class Config {
    public static $config = array();

    public static function init() {
        if (empty(self::$config)) {
            self::database();
            self::server();
        }
        return self::$config;
    }

    public static function database() {
        if (empty(self::$config['database'])) {
            $file = SITE_ROOT.'/config/database.php';
            $database = require $file;
            foreach ($database as $k => $v) {
                self::$config['database.'.$k] = $v;
            }
        }
    }

    public static function server() {
        if (empty(self::$config['server'])) {
            $file = SITE_ROOT.'/config/server.php';
            $database = require $file;
            foreach ($database as $k => $v) {
                self::$config['server.'.$k] = $v;
            }
        }
    }

    public static function get($name) {
        $segments = explode('.', $name);
        if (count($segments) < 2) return null;
        $item = self::$config[$segments[0].'.'.$segments[1]];
        unset($segments[0], $segments[1]);
        $name = implode('.', $segments);
        return array_get($item, $name);
    }

    public static function getConfig() {
        return self::$config;
    }
}