<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Script objects, containing script internals
 *
 * @category Xodoa
 * @package Elastica
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/modules/scripting.html
 */
class Script extends Param
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
     * @var \Elastica\Document
     */
    protected $_upsert;

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
     * @param  string|array|\Elastica\Script        $data
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Script
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
     * @param \Elastica\Document|array $data
     * @return \Elastica\Document
     */
    public function setUpsert($data)
    {
        $document = Document::create($data);
        $this->_upsert = $document;

        return $this;
    }

    /**
     * @return \Elastica\Document
     */
    public function getUpsert()
    {
        return $this->_upsert;
    }

    /**
     * @return bool
     */
    public function hasUpsert()
    {
        return null !== $this->_upsert;
    }

    /**
     * Sets the id of the document the script updates.
     *
     * @param  string            $id
     * @return \Elastica\Script
     */
    public function setId($id)
    {
        return $this->setParam('_id', $id);
    }

    /**
     * Returns id of the document the script updates.
     *
     * @return string|int Document id
     */
    public function getId()
    {
        return ($this->hasParam('_id')) ? $this->getParam('_id') : null;
    }

    /**
     * @return bool
     */
    public function hasId()
    {
        return (bool)$this->getId();
    }

    /**
     * @param array $fields if empty array all options will be returned, field names can be either with underscored either without, i.e. _percolate, routing
     * @param bool $withUnderscore should option keys contain underscore prefix
     * @return array
     */
    public function getOptions(array $fields = array(), $withUnderscore = false)
    {
        if (!empty($fields)) {
            $data = array();
            
            foreach ($fields as $field) {
                $key = '_' . ltrim($field, '_');
                if ($this->hasParam($key) && '' !== (string) $this->getParam($key)) {
                    $data[$key] = $this->getParam($key);
                }
            }
            
        } else {
            $data = $this->getParams();
        }
        
        if (!$withUnderscore) {
            foreach ($data as $key => $value) {
                $data[ltrim($key, '_')] = $value;
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * @param  array                               $data
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Script
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
