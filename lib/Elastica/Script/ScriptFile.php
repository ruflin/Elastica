<?php
namespace Elastica\Script;

/**
 * Script objects, containing script internals.
 *
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @author Nicolas Assing <nicolas.assing@gmail.com>
 * @author Tobias Schultze <http://tobion.de>
 * @author Martin Janser <martin.janser@liip.ch>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
class ScriptFile extends AbstractScript
{
    /**
     * @var string
     */
    private $_scriptFile;

    /**
     * @param string      $scriptFile Script file name
     * @param array|null  $params
     * @param string|null $documentId Document ID the script action should be performed on (only relevant in update context)
     */
    public function __construct($scriptFile, array $params = null, $documentId = null)
    {
        parent::__construct($params, null, $documentId);

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
     * {@inheritdoc}
     */
    protected function getScriptTypeArray()
    {
        return ['file' => $this->_scriptFile];
    }
}
