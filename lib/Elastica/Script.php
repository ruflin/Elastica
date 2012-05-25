<?php
/**
 * User: avasilenko
 * Date: 20.05.12
 * Time: 18:26
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

	public function __construct($script, array $params = null, $lang = null) {
		$this->_script = $script;
		$this->_params = $params;
		$this->_lang = $lang;
	}

	public function setLang($lang) {
		$this->_lang = $lang;
	}

	public function getLang() {
		return $this->_lang;
	}

	public function setParams($params) {
		$this->_params = $params;
	}

	public function getParams() {
		return $this->_params;
	}

	public function setScript($script) {
		$this->_script = $script;
	}

	public function getScript() {
		return $this->_script;
	}
}
