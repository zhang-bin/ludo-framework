<?php

namespace Ludo\Support\Facades;

use Ludo\Log\Logger;

/**
 * @see Logger
 *
 * @method static debug(string $message): void
 * @method static info(string $message): void
 * @method static notice(string $message): void
 * @method static warning(string $message): void
 * @method static error(string $message): void
 * @method static critical(string $message): void
 * @method static alert(string $message): void
 * @method static emergency(string $message): void
 */
class Log extends Facade implements FacadeInterface
{
    /**
     * Get facade accessor
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return Logger::class;
    }
}