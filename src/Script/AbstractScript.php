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
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
abstract class AbstractScript extends AbstractUpdateAction
{
    public const LANG_MOUSTACHE = 'moustache';
    public const LANG_EXPRESSION = 'expression';
    public const LANG_PAINLESS = 'painless';

    /**
     * @var string|null
     */
    private $_lang;

    /**
     * @param string|null $lang       Script language, see constants
     * @param string|null $documentId Document ID the script action should be performed on (only relevant in update context)
     */
    public function __construct(?array $params = null, ?string $lang = null, ?string $documentId = null)
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
     * Factory to create a script object from data structure (reverse toArray).
     *
     * @param AbstractScript|array|string $data
     *
     * @throws InvalidException
     *
     * @return Script|ScriptId
     */
    public static function create($data)
    {
        if ($data instanceof self) {
            return $data;
        }

        if (\is_array($data)) {
            return self::_createFromArray($data);
        }

        if (\is_string($data)) {
            $class = self::class === static::class ? Script::class : static::class;

            return new $class($data);
        }

        throw new InvalidException('Failed to create script. Invalid data passed.');
    }

    public function setLang(string $lang): self
    {
        $this->_lang = $lang;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->_lang;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
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

    /**
     * Returns an array with the script type as key and the script content as value.
     */
    abstract protected function getScriptTypeArray(): array;

    private static function _createFromArray(array $data)
    {
        $params = $data['script']['params'] ?? [];
        $lang = $data['script']['lang'] ?? null;

        if (!\is_array($params)) {
            throw new InvalidException('Script params must be an array');
        }

        if (isset($data['script']['source'])) {
            return new Script(
                $data['script']['source'],
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
}
