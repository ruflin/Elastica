<?php
namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Script objects, containing script internals.
 *
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @author Nicolas Assing <nicolas.assing@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
class ScriptFile extends AbstractScript
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
        parent::__construct($params, $id);

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
     * @param string|array|\Elastica\Script $data
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return self
     */
    public static function create($data)
    {
        if ($data instanceof self) {
            $scriptFile = $data;
        } elseif (is_array($data)) {
            $scriptFile = self::_createFromArray($data);
        } elseif (is_string($data)) {
            $scriptFile = new self($data);
        } else {
            throw new InvalidException('Failed to create scriptFile. Invalid data passed.');
        }

        return $scriptFile;
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
        if (!isset($data['script_file'])) {
            throw new InvalidException("\$data['script_file'] is required");
        }

        $scriptFile = new self($data['script_file']);

        if (isset($data['params'])) {
            if (!is_array($data['params'])) {
                throw new InvalidException("\$data['params'] should be array");
            }
            $scriptFile->setParams($data['params']);
        }

        return $scriptFile;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = array(
            'script_file' => $this->_scriptFile,
        );

        if (!empty($this->_params)) {
            $array['params'] = $this->_params;
        }

        return $array;
    }
}
