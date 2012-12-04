<?php
/**
 * Script objects, containing script internals
 *
 * @category Xodoa
 * @package Elastica
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/modules/scripting.html
 */
class Elastica_Script extends Elastica_Param
{
    const LANG_MVEL   = 'mvel';
    const LANG_JS     = 'js';
    const LANG_GROOVY = 'groovy';
    const LANG_PYTHON = 'python';
    const LANG_NATIVE = 'native';

    /**
     * @var string
     */
    private $_script;
    /**
     * @var string
     */
    private $_lang;

    /**
     * @param string      $script
     * @param array|null  $params
     * @param string|null $lang
     */
    public function __construct($script, array $params = null, $lang = null)
    {
        $this->setScript($script);
        if ($params) {
            $this->setParams($params);
        }
        if ($lang) {
            $this->setLang($lang);
        }
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * @param string $script
     */
    public function setScript($script)
    {
        $this->_script = $script;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->_script;
    }

    /**
     * @param string|array|Elastica_Script|Elastica_Query_Abstract $data
     * @return Elastica_Script
     */
    static public function create($data)
    {

        switch (true) {
            case $data instanceof self;
                return $data;
            case $data instanceof Elastica_Query_Abstract;
                return self::_createFromArray($data->toArray());
            case is_array($data);
                return self::_createFromArray($data);
            case is_string($data):
                return new self($data);
        }

        throw new Elastica_Exception_Invalid('Failed to create script. Invalid data passed.');
    }

    /**
     * @param array $data
     * @return Elastica_Script
     * @throws Elastica_Exception_Invalid
     */
    static protected function _createFromArray(array $data)
    {
        if (!isset($data['script'])) {
            throw new Elastica_Exception_Invalid("\$data['script'] is required");
        }

        $script = new self($data['script']);

        if (isset($data['lang'])) {
            $script->setLang($data['lang']);
        }
        if (isset($data['params'])) {
            if (!is_array($data['params'])) {
                throw new Elastica_Exception_Invalid("\$data['params'] should be array");
            }
            $script->setParams($data['params']);
        }
        return $script;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = array(
            'script' => $this->_script,
        );
        if (!empty($this->_params)) {
            $array['params'] = $this->_params;
        }
        if ($this->_lang) {
            $array['lang'] = $this->_lang;
        }

        return $array;
    }
}
