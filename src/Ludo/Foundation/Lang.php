<?php
namespace Ludo\Foundation;

/**
 * Class Lang
 */
class Lang
{
    private static $lang = array();
    private static $langDir;
    private static $language;

    /**
     * Get the translation for the given key.
     *
     * @param string $key
     * @param array $replace
     * @param string $locale
     * @return string
     * @static
     */
    public static function get($key, array $replace = array(), $locale = null)
    {
        if (is_null($locale)) $locale = self::$language;

        list($group, $item) = explode('.', $key);
        if (isset(self::$lang[$group])) {
            $value = self::$lang[$group][$item];
        } else {
            $filename = LD_LANGUAGE_PATH.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$group.'.lang.php';
            file_exists($filename) && self::$lang[$group] = include $filename;
            $value = self::$lang[$group][$item];
        }

        if (!empty($replace)) {
            foreach ($replace as $k => $v) {
                $value = str_replace(':'.$k, $v, $value);
            }
        }
        return $value;
    }

    public static function init()
    {
        if (isset($_COOKIE['lang'])) {
            $language = $_COOKIE['lang'];
        } else {
            $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        if (($pos = strpos($language, ',')) !== false) {
            $language = substr($language, 0, $pos);
        }

        $language = strtolower($language ? $language : DEFAULT_LANGUAGE);

        $langDir = LD_LANGUAGE_PATH.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR;
        $defaultLangDir = LD_LANGUAGE_PATH.DIRECTORY_SEPARATOR.DEFAULT_LANGUAGE.DIRECTORY_SEPARATOR;

        if (file_exists($langDir)) {
            self::$langDir = $langDir;
        } else if (file_exists($defaultLangDir)) {
            self::$langDir = $defaultLangDir;
        } else {
            throw new \Exception("Language file for [$langDir or $defaultLangDir] does not exist!");
        }
        self::$language = $language;
        include_once self::$langDir.'base.lang.php';
    }
}
