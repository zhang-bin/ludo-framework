<?php
namespace Ludo\View;

class View
{
    /**
     * @var string current template file
     */
	private $tplFile = '';

    /**
     * @var array all data used in template
     */
	private $assignValues = array();

	/**
	 * @var Array  javascript blocks needed by this template
	 */
	private static $jsStrings = array();

	/**
	 * @var Array  javascript files needed by this template
	 */
	private static $jsFiles = array();

	/**
	 * @var Array  css files needed by this template
	 */
	private static $cssFiles = array();

	public function __construct()
    {

	}

	/**
	 * this is an overloading method, which can have one or two arguments
	 * if one: the arg should be a ASSOC array
	 * if two: the 1st arg should be the $varname, the 2nd arg should be $varValue
	 * assign the key => value pair to template
	 * @param String $varName variable name
	 * @param String $varValue variable value
	 * @return $this
	 */
	public function assign($varName, $varValue = '')
    {
		$argNumbers = func_num_args();
		if ($argNumbers == 2) {
			$this->assignValues[$varName] = $varValue;
		} else {
			$this->assignValues = array_merge($this->assignValues, $varName);
		}
		return $this;
	}

	public function display()
    {
		$templateFileWithFullPath = TPL_ROOT.'/'.$this->tplFile.php;
		if (!file_exists($templateFileWithFullPath)) {
			throw new \Exception("File [$templateFileWithFullPath] Not Found");
		}
		extract($this->assignValues);
		include $templateFileWithFullPath;
	}

	/**
	 * Set template file
	 *
	 * @param String $tplFile relative path to TPL_ROOT. eg. user/login, user/register
	 * @return $this
	 */
	public function setFile($tplFile)
    {
		$this->tplFile = $tplFile;
		$this->assignValues = array();
		return $this;
	}

	/**
	 * add Js, Css files to the template
	 * @param String $type  type of file: 'css' or 'js'
	 * @param String $file  file string
	 */
	public static function addResource($file, $type = 'css')
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
	public static function clearResource($type = '')
    {
		switch ($type) {
			case 'js':
				self::$jsFiles = array();
				self::$jsStrings = array();
				break;
			case 'css':
				self::$cssFiles = array();
				break;
			default:
				self::$jsFiles = array();
				self::$jsStrings = array();
				self::$cssFiles = array();
				break;
		}
	}

	/**
	 * Load all js files used by this template.
	 */
	public static function loadJs()
    {
		echo implode("\n", self::$jsFiles);
		echo implode("\n", self::$jsStrings);
		self::clearResource('js');
	}

	/**
	 * Load all css files used by this template.
	 */
	public static function loadCss()
    {
		echo implode("\n", self::$cssFiles);
		self::clearResource('css');
	}

	/**
	 * start to cache js block contents
	 */
	public static function startJs()
    {
		ob_start();
	}

	/**
	 * start to cache js block contents
	 */
	public static function endJs()
    {
		self::$jsStrings[] = ob_get_clean();
	}
}
