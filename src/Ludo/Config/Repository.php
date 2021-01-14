<?php

namespace Ludo\Config;

/**
 * Class Repository
 *
 * @package Ludo\Config
 */
class Repository
{
    /**
     * @var array $config config data
     */
    public array $config = [];

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $dir = SITE_ROOT . '/config/';
        $this->readDirectory($dir);
    }

    /**
     * Read config file
     *
     * @param string $filename config filename
     */
    private function readFile(string $filename): void
    {
        if (file_exists($filename) && is_readable($filename)) {
            $config = require $filename;
            $basename = basename($filename, '.php');
            foreach ($config as $k => $v) {
                $this->config[$basename][$k] = $v;
            }
        }
    }

    /**
     * Read config directory
     *
     * @param string $dir config directory
     */
    private function readDirectory(string $dir): void
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file[0] == '.' || $file[0] == '..') {
                continue;
            }
            $filename = $dir . $file;
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
     * @param ?string $name config key
     * @return mixed
     */
    public function get(string $name = null)
    {
        return array_get($this->config, $name);
    }

    /**
     * Set config value
     *
     * @param string $name config key
     * @param mixed $value default value
     */
    public function set(string $name, $value): void
    {
        array_set($this->config, $name, $value);
    }
}