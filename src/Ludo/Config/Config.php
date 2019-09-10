<?php
namespace Ludo\Config;

/**
 * Class Config
 *
 * @package Ludo\Config
 */
class Config
{
    /**
     * @var array $config config data
     */
    public static $config = array();

    /**
     * @var Config $instance config instance
     */
    private static $instance;

    /**
     * Get config instance
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $dir = SITE_ROOT.'/config/';
        $this->readDirectory($dir);
    }

    /**
     * Read config file
     *
     * @param string $filename
     */
    private function readFile(string $filename): void
    {
        if (file_exists($filename) && is_readable($filename)) {
            $config = require $filename;
            $basename = basename($filename, '.php');
            foreach ($config as $k => $v) {
                self::$config[$basename][$k] = $v;
            }
        }
    }

    /**
     * Read config directory
     *
     * @param string $dir
     */
    private function readDirectory(string $dir): void
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file[0] == '.' || $file[0] == '..') {
                continue;
            }
            $filename = $dir.$file;
            $ext = ext($filename);
            if ($ext != 'php') {
                continue;
            }

            $this->readFile($filename);
        }
    }

    /**
     * Get config value by key
     *
     * @param $name
     * @return mixed
     */
    public function get($name)
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

    /**
     * Get all config data
     *
     * @return array
     */
    public function getConfig()
    {
        return self::$config;
    }
}