<?php

namespace Elastica;

use Elastica\Bulk\Action;
use Elastica\Bulk\Action\AbstractDocument as AbstractDocumentAction;
use Elastica\Bulk\Response as BulkResponse;
use Elastica\Bulk\ResponseSet;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use Elastica\Exception\InvalidException;
use Elastica\Script\AbstractScript;

class Bulk
{
    public const DELIMITER = "\n";

    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var Action[]
     */
    protected $_actions = [];

    /**
     * @var string|null
     */
    protected $_index;

    /**
     * @var string|null
     */
    protected $_type;

    /**
     * @var array request parameters to the bulk api
     */
    protected $_requestParams = [];

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param Index|string $index
     *
     * @return $this
     */
    public function setIndex($index): self
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        $this->_index = (string) $index;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIndex()
    {
        return $this->_index;
    }

    public function hasIndex(): bool
    {
        return null !== $this->getIndex() && '' !== $this->getIndex();
    }

    public function getPath(): string
    {
        $path = '';
        if ($this->hasIndex()) {
            $path .= $this->getIndex().'/';
        }
        $path .= '_bulk';

        return $path;
    }

    /**
     * @return $this
     */
    public function addAction(Action $action): self
    {
        $this->_actions[] = $action;

        return $this;
    }

    /**
     * @param Action[] $actions
     *
     * @return $this
     */
    public function addActions(array $actions): self
    {
        foreach ($actions as $action) {
            $this->addAction($action);
        }

        return $this;
    }

    /**
     * @return Action[]
     */
    public function getActions(): array
    {
        return $this->_actions;
    }

    /**
     * @return $this
     */
    public function addDocument(Document $document, ?string $opType = null): self
    {
        $action = AbstractDocumentAction::create($document, $opType);

        return $this->addAction($action);
    }

    /**
     * @param Document[] $documents
     *
     * @return $this
     */
    public function addDocuments(array $documents, ?string $opType = null): self
    {
        foreach ($documents as $document) {
            $this->addDocument($document, $opType);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addScript(AbstractScript $script, ?string $opType = null): self
    {
        $action = AbstractDocumentAction::create($script, $opType);

        return $this->addAction($action);
    }

    /**
     * @param Document[] $scripts
     * @param mixed|null $opType
     *
     * @return $this
     */
    public function addScripts(array $scripts, $opType = null): self
    {
        foreach ($scripts as $document) {
            $this->addScript($document, $opType);
        }

        return $this;
    }

    /**
     * @param array|\Elastica\Document|\Elastica\Script\AbstractScript $data
     *
     * @return $this
     */
    public function addData($data, ?string $opType = null)
    {
        if (!\is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $actionData) {
            if ($actionData instanceof AbstractScript) {
                $this->addScript($actionData, $opType);
            } elseif ($actionData instanceof Document) {
                $this->addDocument($actionData, $opType);
            } else {
                throw new \InvalidArgumentException('Data should be a Document, a Script or an array containing Documents and/or Scripts');
            }
        }

        return $this;
    }

    /**
     * @throws InvalidException
     *
     * @return $this
     */
    public function addRawData(array $data): self
    {
        foreach ($data as $row) {
            if (\is_array($row)) {
                $opType = \key($row);
                $metadata = \reset($row);
                if (Action::isValidOpType($opType)) {
                    // add previous action
                    if (isset($action)) {
                        $this->addAction($action);
                    }
                    $action = new Action($opType, $metadata);
                } elseif (isset($action)) {
                    $action->setSource($row);
                    $this->addAction($action);
                    $action = null;
                } else {
                    throw new InvalidException('Invalid bulk data, source must follow action metadata');
                }
            } else {
                throw new InvalidException('Invalid bulk data, should be array of array, Document or Bulk/Action');
            }
        }

        // add last action if available
        if (isset($action)) {
            $this->addAction($action);
        }

        return $this;
    }

    /**
     * Set a url parameter on the request bulk request.
     *
     * @param string $name  name of the parameter
     * @param mixed  $value value of the parameter
     *
     * @return $this
     */
    public function setRequestParam(string $name, $value): self
    {
        $this->_requestParams[$name] = $value;

        return $this;
    }

    /**
     * Set the amount of time that the request will wait the shards to come on line.
     * Requires Elasticsearch version >= 0.90.8.
     *
     * @param string $time timeout in Elasticsearch time format
     *
     * @return $this
     */
    public function setShardTimeout(string $time): self
    {
        return $this->setRequestParam('timeout', $time);
    }

    public function toString(): string
    {
        $data = '';
        foreach ($this->getActions() as $action) {
            $data .= $action->toString();
        }

        return $data;
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->getActions() as $action) {
            foreach ($action->toArray() as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function send(): ResponseSet
    {
        $path = $this->getPath();
        $data = $this->toString();

        $response = $this->_client->request($path, Request::POST, $data, $this->_requestParams, Request::NDJSON_CONTENT_TYPE);

        return $this->_processResponse($response);
    }

    /**
     * @throws ResponseException
     * @throws InvalidException
     */
    protected function _processResponse(Response $response): ResponseSet
    {
        $responseData = $response->getData();

        $actions = $this->getActions();

        $bulkResponses = [];

        if (isset($responseData['items']) && \is_array($responseData['items'])) {
            foreach ($responseData['items'] as $key => $item) {
                if (!isset($actions[$key])) {
                    throw new InvalidException('No response found for action #'.$key);
                }

                $action = $actions[$key];

                $opType = \key($item);
                $bulkResponseData = \reset($item);

                if ($action instanceof AbstractDocumentAction) {
                    $data = $action->getData();
                    if ($data instanceof Document && $data->isAutoPopulate()
                        || $this->_client->getConfigValue(['document', 'autoPopulate'], false)
                    ) {
                        if (!$data->hasId() && isset($bulkResponseData['_id'])) {
                            $data->setId($bulkResponseData['_id']);
                        }
                        if (isset($bulkResponseData['_version'])) {
                            $data->setVersion($bulkResponseData['_version']);
                        }
                    }
                }

                $bulkResponses[] = new BulkResponse($bulkResponseData, $action, $opType);
            }
        }

        $bulkResponseSet = new ResponseSet($response, $bulkResponses);

        if ($bulkResponseSet->hasError()) {
            throw new BulkResponseException($bulkResponseSet);
        }

        return $bulkResponseSet;
    }
}
