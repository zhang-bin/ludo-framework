<?php

namespace Ludo\Support;

use Ludo\Database\Dao;

class Factory
{
    /**
     * @param string $name
     * @param string $connectionName
     * @return Dao
     */
    public static function dao(string $name, string $connectionName = null): Dao
    {
        $daoName = trim($name, '/') . 'Dao';
        $daoName = ucfirst($daoName);
        $dirs = ClassLoader::getDirectories();
        foreach ($dirs as $dir) {
            $filename = $dir . DIRECTORY_SEPARATOR . $daoName . php;
            if (file_exists($filename)) {
                return new $daoName;
            }
        }
        return new \BaseDao($name, $connectionName);
    }
}
