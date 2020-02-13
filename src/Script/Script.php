<?php

namespace Elastica\Script;

/**
 * Inline script.
 *
 * @author avasilenko <aa.vasilenko@gmail.com>
 * @author Tobias Schultze <http://tobion.de>
 * @author Martin Janser <martin.janser@liip.ch>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
class Script extends AbstractScript
{
    /**
     * @var string
     */
    private $_scriptCode;

    /**
     * @param string      $scriptCode Script source code
     * @param string|null $documentId Document ID the script action should be performed on (only relevant in update context)
     */
    public function __construct(string $scriptCode, ?array $params = null, ?string $lang = null, ?string $documentId = null)
    {
        parent::__construct($params, $lang, $documentId);

        $this->setScript($scriptCode);
    }

    public function setScript(string $scriptCode): self
    {
        $this->_scriptCode = $scriptCode;

        return $this;
    }

    public function getScript(): string
    {
        return $this->_scriptCode;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScriptTypeArray(): array
    {
        return ['source' => $this->_scriptCode];
    }
}
