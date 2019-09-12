<?php
namespace Ludo\Support\Facades;

interface FacadeInterface
{
    /**
     * Get facade accessor name
     *
     * @return string
     */
    public static function getFacadeAccessor(): string;
}