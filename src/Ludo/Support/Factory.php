<?php
namespace Ludo\Support;

class Factory
{
    /**
     * @param string $name
     * @param string $connectionName
     * @return \Ludo\Database\Dao
     */
    public static function dao($name, $connectionName = null)
    {
        $daoName = trim($name, '/').'Dao';
        $daoName = ucfirst($daoName);
        $dirs = ClassLoader::getDirectories();
        foreach ($dirs as $dir) {
            $filename = $dir.DIRECTORY_SEPARATOR.$daoName.php;
            if (file_exists($filename)) {
                return new $daoName;
            }
        }
        return new \BaseDao($name, $connectionName);
    }
}
