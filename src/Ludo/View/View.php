<?php
namespace Ludo\View;

class View {
	/** current template file */
	private $tplFile = '';

	/** all data used in template */
	private $assignValues = array();

	function __construct() {

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
	function assign($varName, $varValue = '') {
		$argNumbers = func_num_args();
		if ($argNumbers == 2) {
			$this->assignValues[$varName] = $varValue;
		} else {
			$this->assignValues = array_merge($this->assignValues, $varName);
		}
		return $this;
	}

	function display() {
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
	function setFile($tplFile) {
		$this->tplFile = $tplFile;
		$this->assignValues = array();
		return $this;
	}
}
