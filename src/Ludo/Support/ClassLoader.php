<?php

namespace Ludo\Support;


/**
 * Class ClassLoader
 *
 * @package Ludo\Support
 */
class ClassLoader
{
    /**
     * @var array $directories registered directories
     */
    protected static array $directories = [];

    /**
     * @var bool $registered Indicates if a ClassLoader has been registered
     */
    protected static bool $registered = false;

    /**
     * Load the given class file.
     *
     * @param string $class class name
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
     * @param string $className class name
     * @return string
     */
    public static function normalizeClass(string $className): string
    {
        if ($className[0] == '\\') {
            $className = substr($className, 1);
        }

        if (start_with($className, 'App\\')) {
            $className = lcfirst($className);
        }

        return str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className) . '.php';
    }

    /**
     * Register the given class loader on the autoloader stack.
     *
     * @return void
     */
    public static function register(): void
    {
        if (!self::$registered) {
            self::$registered = spl_autoload_register(['\Ludo\Support\ClassLoader', 'load']);
        }
    }

    /**
     * Add directories to the class loader.
     *
     * @param array|string $directories class directory
     * @return void
     */
    public static function addDirectories(array|string $directories): void
    {
        self::$directories = array_merge(self::$directories, (array)$directories);
        self::$directories = array_unique(self::$directories);
    }

    /**
     * Remove directories from the class loader.
     *
     * @param array|string|null $directories class directory
     * @return void
     */
    public static function removeDirectories(array|string $directories = null): void
    {
        if (is_null($directories)) {
            static::$directories = [];
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
