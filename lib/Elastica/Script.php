<?php
namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Script objects, containing script internals.
 *
 * @author avasilenko <aa.vasilenko@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
class Script extends AbstractUpdateAction
{
    const LANG_MVEL = 'mvel';
    const LANG_JS = 'js';
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
    public function __construct($script, array $params = null, $lang = null, $id = null)
    {
        $this->setScript($script);

        if ($params) {
            $this->setParams($params);
        }

        if ($lang) {
            $this->setLang($lang);
        }

        if ($id) {
            $this->setId($id);
        }
    }

    /**
     * @param string $lang
     *
     * @return $this
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;

        return $this;
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
     *
     * @return $this
     */
    public function setScript($script)
    {
        $this->_script = $script;

        return $this;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->_script;
    }

    /**
     * @param string|array|\Elastica\Script $data
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return self
     */
    public static function create($data)
    {
        if ($data instanceof self) {
            $script = $data;
        } elseif (is_array($data)) {
            $script = self::_createFromArray($data);
        } elseif (is_string($data)) {
            $script = new self($data);
        } else {
            throw new InvalidException('Failed to create script. Invalid data passed.');
        }

        return $script;
    }

    /**
     * @param array $data
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return self
     */
    protected static function _createFromArray(array $data)
    {
        if (!isset($data['script'])) {
            throw new InvalidException("\$data['script'] is required");
        }

        $script = new self($data['script']);

        if (isset($data['lang'])) {
            $script->setLang($data['lang']);
        }

        if (isset($data['params'])) {
            if (!is_array($data['params'])) {
                throw new InvalidException("\$data['params'] should be array");
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
