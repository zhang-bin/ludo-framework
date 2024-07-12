<?php

namespace Ludo\Support;


/**
 * Class AliasLoader
 *
 * @package Ludo\Support
 */
class AliasLoader
{
    /**
     * @var array $aliases array of class aliases
     */
    protected array $aliases;

    /**
     * @var bool $registered Indicates if a loader has been registered
     */
    protected bool $registered = false;

    /**
     * @var AliasLoader $instance The singleton instance of the loader
     */
    protected static AliasLoader $instance;

    /**
     * Create a new class alias loader instance.
     *
     * @param array $aliases aliases
     */
    public function __construct(array $aliases = [])
    {
        $this->aliases = $aliases;
    }

    /**
     * Get or create the singleton alias loader instance.
     *
     * @param array $aliases aliases
     * @return AliasLoader
     */
    public static function getInstance(array $aliases = []): AliasLoader
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($aliases);
        }

        $aliases = array_merge(self::$instance->getAliases(), $aliases);

        self::$instance->setAliases($aliases);

        return self::$instance;
    }

    /**
     * Load a class alias if it is registered.
     *
     * @param string $alias aliases
     * @return bool
     */
    public function load(string $alias): bool
    {
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }
        return false;
    }

    /**
     * Add an alias to the loader.
     *
     * @param string $class class name
     * @param string $alias alias name
     * @return void
     */
    public function alias(string $class, string $alias): void
    {
        $this->aliases[$class] = $alias;
    }

    /**
     * Register the loader on the autoloader stack.
     *
     * @return void
     */
    public function register(): void
    {
        if (!$this->registered) {
            $this->prependToLoaderStack();
            $this->registered = true;
        }
    }

    /**
     * Prepend the load method to the autoloader stack.
     *
     * @return void
     */
    protected function prependToLoaderStack(): void
    {
        spl_autoload_register([$this, 'load'], true, true);
    }

    /**
     * Get the registered aliases.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Set the registered aliases.
     *
     * @param array $aliases aliases
     * @return void
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * Indicates if the loader has been registered.
     *
     * @return bool
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Set the "registered" state of the loader.
     *
     * @param bool $value registered flag
     * @return void
     */
    public function setRegistered(bool $value): void
    {
        $this->registered = $value;
    }
}
