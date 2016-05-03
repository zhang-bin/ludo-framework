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
            $language = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        if (($pos = strpos($language, ',')) !== false) {
            $language = substr($language, 0, $pos);
            setcookie('lang', $language, null, '/');
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

    /**
     * 语言包差异
     *
     * @param $baseDir string 基础路径
     * @param $base string 基础语言包
     * @param $diff string 差异语言包
     * @return array
     */
    public static function diff($baseDir, $base, $diff) {
        $baseLangDir = $baseDir.DIRECTORY_SEPARATOR.$base.DIRECTORY_SEPARATOR;
        $diffLangDir = $baseDir.DIRECTORY_SEPARATOR.$diff.DIRECTORY_SEPARATOR;

        $baseLanguages = $diffLanguages = array();
        $files = scandir($baseLangDir);
        foreach ($files as $file) {
            if ($file[0] == '.') continue;
            $filename = $baseLangDir.$file;
            $ext = ext($filename);
            if ($ext != 'php') continue;
            $file = explodeSafe($file, '.')[0];
            $baseLanguages[$file] = require $filename;
        }

        $files = scandir($diffLangDir);
        foreach ($files as $file) {
            if ($file[0] == '.') continue;
            $filename = $diffLangDir.$file;
            $ext = ext($filename);
            if ($ext != 'php') continue;
            $file = explodeSafe($file, '.')[0];
            $diffLanguages[$file] = require $filename;
        }

        $result = array();
        foreach ($baseLanguages as $filename => $languages) {
            if ($filename == 'base') continue;
            foreach ($languages as $k => $v) {
                if (empty($diffLanguages[$filename][$k])) {
                    $result[$filename][$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * 合并差异语言
     *
     * @param $baseDir string 基础路径
     * @param $language string 语言
     * @param $data array 翻译数据
     */
    public static function merge($baseDir, $language, $data) {
        $baseLangDir = $baseDir.DIRECTORY_SEPARATOR.DEFAULT_LANGUAGE.DIRECTORY_SEPARATOR;

        foreach ($data as $filename => $datum) {
            $baseLangFilename = $baseLangDir.$filename.'.lang.php';
            if (!file_exists($baseLangFilename)) continue;

            $diffLangFilename = $baseDir.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$filename.'.lang.php';
            $diffData = array();
            if (file_exists($diffLangFilename)) {
                $diffData = require $diffLangFilename;
            }

            foreach ($datum as $k => $v) {
                $diffData[$k] = $v;
            }
            $str = var_export($diffData, true);
            $str = "<?php\nreturn ".$str.';';
            file_put_contents($diffLangFilename, $str);
        }
    }
}
