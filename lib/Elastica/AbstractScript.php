<?php
namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Base class for Script object.
 *
 * @author Nicolas Assing <nicolas.assing@gmail.com>
 * @author avasilenko <aa.vasilenko@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
abstract class AbstractScript extends AbstractUpdateAction
{
    const LANG_MVEL = 'mvel';
    const LANG_JS = 'js';
    const LANG_GROOVY = 'groovy';
    const LANG_PYTHON = 'python';
    const LANG_NATIVE = 'native';

    protected $script;

    protected $lang;

    /**
     * @param array|null $params
     * @param string     $id
     */
    public function __construct($script, array $params = null, $lang = null, $id = null)
    {
        $this->setScript($script);

        if ($params) {
            $this->setParams($params);
        }

        if ($id) {
            $this->setId($id);
        }

        if ($lang) {
            $this->setLang($lang);
        }
    }

    /**
     * @return mixed
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @param $script
     *
     * @return $this
     */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param $lang
     *
     * @return $this
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @param string|array|\Elastica\AbstractScript $data
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
            $class = get_called_class();
            $script = new $class($data);
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
        $class = get_called_class();
        $type = Util::getParamName($class);

        if (!isset($data[$type])) {
            throw new InvalidException("\$data['script'] is required");
        }

        /** @var self $script */
        $script = new $class($data[$type]);

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
            Util::getParamName($this) => $this->script,
        );

        if (!empty($this->getParams())) {
            $array['params'] = $this->getParams();
        }

        if ($this->getLang()) {
            $array['lang'] = $this->getLang();
        }

        return $array;
    }
}
