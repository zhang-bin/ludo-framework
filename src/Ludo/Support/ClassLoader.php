<?php

namespace Ludo\Support;

class ClassLoader
{

    /**
     * The registered directories.
     *
     * @var array
     */
    protected static $directories = array();

    /**
     * Indicates if a ClassLoader has been registered.
     *
     * @var bool
     */
    protected static $registered = false;

    /**
     * Load the given class file.
     *
     * @param string $class
     */
    public static function load(string $class): void
    {
        $class = self::normalizeClass($class);

        foreach (self::$directories as $directory) {
            if (file_exists($path = $directory . DIRECTORY_SEPARATOR . $class)) {
                require_once $path;
            }
        }
    }

    /**
     * Get the normal file name for a class.
     *
     * @param string $className
     * @return string
     */
    public static function normalizeClass(string $className): string
    {
        if ($className[0] == '\\') {
            $className = substr($className, 1);
        }

        return str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
    }

    /**
     * Register the given class loader on the auto-loader stack.
     *
     * @return void
     */
    public static function register(): void
    {
        if (!self::$registered) {
            self::$registered = spl_autoload_register(array('\Ludo\Support\ClassLoader', 'load'));
        }
    }

    /**
     * Add directories to the class loader.
     *
     * @param string|array $directories
     * @return void
     */
    public static function addDirectories($directories): void
    {
        self::$directories = array_merge(self::$directories, (array)$directories);
        self::$directories = array_unique(self::$directories);
    }

    /**
     * Remove directories from the class loader.
     *
     * @param string|array $directories
     * @return void
     */
    public static function removeDirectories($directories = null): void
    {
        if (is_null($directories)) {
            static::$directories = array();
        } else {
            $directories = (array)$directories;

            self::$directories = array_filter(self::$directories, function ($directory) use ($directories) {
                return (!in_array($directory, $directories));
            });
        }
    }

    /**
     * Gets all the directories registered with the loader.
     *
     * @return array
     */
    public static function getDirectories(): array
    {
        return self::$directories;
    }
}
