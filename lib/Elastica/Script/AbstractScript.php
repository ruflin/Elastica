<?php
namespace Elastica\Script;

use Elastica\AbstractUpdateAction;
use Elastica\Exception\InvalidException;

/**
 * Base class for Script object.
 *
 * Wherever scripting is supported in the Elasticsearch API, scripts can be referenced as "inline", "id" or "file".
 *
 * @author Nicolas Assing <nicolas.assing@gmail.com>
 * @author Tobias Schultze <http://tobion.de>
 * @author Martin Janser <martin.janser@liip.ch>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
abstract class AbstractScript extends AbstractUpdateAction
{
    const LANG_MVEL = 'mvel';
    const LANG_JS = 'js';
    const LANG_GROOVY = 'groovy';
    const LANG_PYTHON = 'python';
    const LANG_NATIVE = 'native';
    const LANG_EXPRESSION = 'expression';
    const LANG_PAINLESS = 'painless';

    /**
     * @var string
     */
    private $_lang;

    /**
     * Factory to create a script object from data structure (reverse toArray).
     *
     * @param string|array|AbstractScript $data
     *
     * @throws InvalidException
     *
     * @return Script|ScriptFile|ScriptId
     */
    public static function create($data)
    {
        if ($data instanceof self) {
            return $data;
        }

        if (is_array($data)) {
            return self::_createFromArray($data);
        }

        if (is_string($data)) {
            $class = self::class === get_called_class() ? Script::class : get_called_class();

            return new $class($data);
        }

        throw new InvalidException('Failed to create script. Invalid data passed.');
    }

    private static function _createFromArray(array $data)
    {
        $params = isset($data['script']['params']) ? $data['script']['params'] : [];
        $lang = isset($data['script']['lang']) ? $data['script']['lang'] : null;

        if (!is_array($params)) {
            throw new InvalidException('Script params must be an array');
        }

        if (isset($data['script']['inline'])) {
            return new Script(
                $data['script']['inline'],
                $params,
                $lang
            );
        }

        if (isset($data['script']['file'])) {
            return new ScriptFile(
                $data['script']['file'],
                $params,
                $lang
            );
        }

        if (isset($data['script']['id'])) {
            return new ScriptId(
                $data['script']['id'],
                $params,
                $lang
            );
        }

        throw new InvalidException('Failed to create script. Invalid data passed.');
    }

    /**
     * @param array|null  $params
     * @param string|null $lang       Script language, see constants
     * @param string|null $documentId Document ID the script action should be performed on (only relevant in update context)
     */
    public function __construct(array $params = null, $lang = null, $documentId = null)
    {
        if ($params) {
            $this->setParams($params);
        }

        if (null !== $lang) {
            $this->setLang($lang);
        }

        if (null !== $documentId) {
            $this->setId($documentId);
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
     * @return string|null
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * Returns an array with the script type as key and the script content as value.
     *
     * @return array
     */
    abstract protected function getScriptTypeArray();

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = $this->getScriptTypeArray();

        if (!empty($this->_params)) {
            $array['params'] = $this->_convertArrayable($this->_params);
        }

        if (null !== $this->_lang) {
            $array['lang'] = $this->_lang;
        }

        return ['script' => $array];
    }
}
