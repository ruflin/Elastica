<?php
/**
 * Script objects, containing script internals
 *
 * @category Xodoa
 * @package Elastica
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/modules/scripting.html
 */
class Elastica_Script {
	/**
	 * @var string
	 */
	private $_script;
	/**
	 * @var string
	 */
	private $_lang;
	/**
	 * @var array
	 */
	private $_params;

	/**
	 * @param string $script
	 * @param array|null $params
	 * @param string|null $lang
	 */
	public function __construct($script, array $params = null, $lang = null) {
		$this->_script = $script;
		$this->_params = $params;
		$this->_lang = $lang;
	}

	/**
	 * @param string $lang
	 */
	public function setLang($lang) {
		$this->_lang = $lang;
	}

	/**
	 * @return string
	 */
	public function getLang() {
		return $this->_lang;
	}

	/**
	 * @param array $params
	 */
	public function setParams($params) {
		$this->_params = $params;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}

	/**
	 * @param string $script
	 */
	public function setScript($script) {
		$this->_script = $script;
	}

	/**
	 * @return string
	 */
	public function getScript() {
		return $this->_script;
	}
}
