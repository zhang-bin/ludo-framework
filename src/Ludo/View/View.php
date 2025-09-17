<?php

namespace Ludo\View;

use Exception;

class View
{
    /**
     * @var string $tplFile current template file
     */
    private string $tplFile = '';

    /**
     * @var array $assignValues all data used in template
     */
    private array $assignValues = [];

    /**
     * @var array $jsStrings javascript blocks needed by this template
     */
    private static array $jsStrings = [];

    /**
     * @var array $jsFiles javascript files needed by this template
     */
    private static array $jsFiles = [];

    /**
     * @var array $cssFiles css files needed by this template
     */
    private static array $cssFiles = [];

    public function __construct()
    {

    }

    /**
     * This is an overloading method, which can have one or two arguments
     * if one: the arg should be a ASSOC array
     * if two: the 1st arg should be the $varName, the 2nd arg should be $varValue
     * assign the key => value pair to template
     *
     * @param string $varName variable name
     * @param mixed $varValue variable value
     * @return $this
     */
    public function assign(string $varName, mixed $varValue): View
    {
        $argNumbers = func_num_args();
        if ($argNumbers == 2) {
            $this->assignValues[$varName] = $varValue;
        } else {
            $this->assignValues = array_merge($this->assignValues, (array)$varName);
        }
        return $this;
    }

    /**
     * Display template file
     *
     * @throws Exception
     */
    public function display(): void
    {
        $templateFileWithFullPath = LD_TPL_ROOT . '/' . $this->tplFile . php;
        if (!file_exists($templateFileWithFullPath)) {
            throw new Exception("File [$templateFileWithFullPath] Not Found");
        }
        extract($this->assignValues);
        include $templateFileWithFullPath;
    }

    /**
     * Set template file
     *
     * @param string $tplFile relative path to TPL_ROOT. e.g. user/login, user/register
     * @return $this
     */
    public function setFile(string $tplFile): View
    {
        $this->tplFile = $tplFile;
        $this->assignValues = [];
        return $this;
    }

    /**
     * Add Js, Css files to the template
     *
     * @param String $type type of file: 'css' or 'js'
     * @param String $file file string
     */
    public static function addResource(string $file, string $type = 'css'): void
    {
        $file = trim($file);
        if ($type == 'js') {
            self::$jsFiles[$file] = $file;
        } else {
            self::$cssFiles[$file] = $file;
        }
    }

    /**
     * Clear all Js, Css files in template
     *
     * @param string $type
     */
    public static function clearResource(string $type = ''): void
    {
        switch ($type) {
            case 'js':
                self::$jsFiles = [];
                self::$jsStrings = [];
                break;
            case 'css':
                self::$cssFiles = [];
                break;
            default:
                self::$jsFiles = [];
                self::$jsStrings = [];
                self::$cssFiles = [];
                break;
        }
    }

    /**
     * Load all js files used by this template.
     */
    public static function loadJs(): void
    {
        echo implode("\n", self::$jsFiles);
        echo implode("\n", self::$jsStrings);
        self::clearResource('js');
    }

    /**
     * Load all css files used by this template.
     */
    public static function loadCss(): void
    {
        echo implode("\n", self::$cssFiles);
        self::clearResource('css');
    }

    /**
     * Start to cache js block contents
     */
    public static function startJs(): void
    {
        ob_start();
    }

    /**
     * Start to cache js block contents
     */
    public static function endJs(): void
    {
        self::$jsStrings[] = ob_get_clean();
    }
}
