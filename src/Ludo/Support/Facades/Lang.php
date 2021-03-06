<?php

namespace Ludo\Support\Facades;

use Ludo\Translation\Translator;


/**
 * @see Translator
 *
 * @method static setLanguage($lang)
 * @method static get(string $key, array $replace = [], string $locale = null)
 * @method static diff(string $base, string $baseLang, string $diffLang)
 * @method static merge(string $base, string $lang, array $data)
 */
class Lang extends Facade implements FacadeInterface
{
    /**
     * Get facade accessor
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return Translator::class;
    }
}