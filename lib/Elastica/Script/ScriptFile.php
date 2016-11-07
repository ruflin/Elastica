<?php
namespace Elastica\Script;

use Elastica\Exception\InvalidException;

/**
 * Script objects, containing script internals.
 *
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @author Nicolas Assing <nicolas.assing@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
class ScriptFile extends Script
{
    /**
     * @var string
     */
    private $_scriptFile;

    /**
     * @param string     $scriptFile
     * @param array|null $params
     * @param null       $id
     */
    public function __construct($scriptFile, array $params = null, $id = null)
    {
        parent::__construct(null, $params, null, $id);

        $this->setScriptFile($scriptFile);
    }

    /**
     * @param string $scriptFile
     *
     * @return $this
     */
    public function setScriptFile($scriptFile)
    {
        $this->_scriptFile = $scriptFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getScriptFile()
    {
        return $this->_scriptFile;
    }

    /**
     * @param string|array|\Elastica\ScriptFile $data
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return self
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
            return new self($data);
        }

        throw new InvalidException('Failed to create scriptFile. Invalid data passed.');
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
        if (!isset($data['script']['file'])) {
            throw new InvalidException("\$data['script']['file'] is required");
        }

        $scriptFile = new self($data['script']['file']);

        if (isset($data['script']['params'])) {
            if (!is_array($data['script']['params'])) {
                throw new InvalidException("\$data['script']['params'] should be array");
            }
            $scriptFile->setParams($data['script']['params']);
        }

        return $scriptFile;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'script' => [
                'file' => $this->_scriptFile,
            ],
        ];

        if (!empty($this->_params)) {
            $array['script']['params'] = $this->_params;
        }

        return $array;
    }
}
