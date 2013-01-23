<?php

namespace Elastica;

use Elastica\Bulk\ResponseSet;
use Elastica\Document;
use Elastica\Exception\BulkResponseException;
use Elastica\Exception\InvalidException;
use Elastica\Request;
use Elastica\Response;
use Elastica\Type;
use Elastica\Index;
use Elastica\Bulk\Action;
use Elastica\Client;
use Elastica\Bulk\Action\AbstractDocument as AbstractDocumentAction;

class Bulk
{
    const DELIMITER = "\n";

    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var Action[]
     */
    protected $_actions = array();

    /**
     * @var string
     */
    protected $_index = '';

    /**
     * @var string
     */
    protected $_type = '';

    /**
     * @param \Elastica\Client $client
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * @param string|Index $index
     * @return $this
     */
    public function setIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        $this->_index = (string) $index;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * @return bool
     */
    public function hasIndex()
    {
        return '' !== $this->getIndex();
    }

    /**
     * @param string|Type $type
     * @return $this
     */
    public function setType($type)
    {
        if ($type instanceof Type) {
            $this->setIndex($type->getIndex()->getName());
            $type = $type->getName();
        }

        $this->_type = (string) $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return bool
     */
    public function hasType()
    {
        return '' !== $this->_type;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $path = '/';
        if ($this->hasIndex()) {
            $path.= $this->getIndex() . '/';
            if ($this->hasType()) {
                $path.= $this->getType() . '/';
            }
        }
        $path.= '_bulk';
        return $path;
    }

    /**
     * @param Action $action
     * @return $this
     */
    public function addAction(Action $action)
    {
        $this->_actions[] = $action;
        return $this;
    }

    /**
     * @param Action[] $actions
     * @return $this
     */
    public function addActions(array $actions)
    {
        foreach ($actions as $action) {
            $this->addAction($action);
        }

        return $this;
    }

    /**
     * @return Action[]
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * @param \Elastica\Document $document
     * @param string $opType
     * @return $this
     */
    public function addDocument(Document $document, $opType = null)
    {
        $action = AbstractDocumentAction::create($document, $opType);

        return $this->addAction($action);
    }

    /**
     * @param array $documents
     * @param string $opType
     * @return $this
     */
    public function addDocuments(array $documents, $opType = null)
    {
        foreach ($documents as $document) {
            $this->addDocument($document, $opType);
        }

        return $this;
    }

    /**
     * @param Client $client
     * @param array $data
     * @return Bulk
     * @throws Exception\InvalidException
     */
    public static function create(Client $client, array $data)
    {
        $bulk = new self($client);

        foreach ($data as $row) {
            if ($row instanceof Document) {
                $bulk->addDocument($row);
                $action = null;
            } else if ($row instanceof Action) {
                $bulk->addAction($row);
                $action = null;
            } else if (is_array($row)) {
                $opType = key($row);
                $metadata = reset($row);
                if (Document::isValidOpType($opType)) {
                    // add previous action
                    if (isset($action)) {
                        $bulk->addAction($action);
                    }
                    $action = new Action($opType, $metadata);
                } else if (isset($action)) {
                    $action->setSource($metadata);
                } else {
                    throw new InvalidException('Invalid bulk data, source must follow action metadata');
                }
            } else {
                throw new InvalidException('Invalid bulk data, should be array of array, Document or Bulk/Action');
            }
        }

        // add last action if available
        if (isset($action)) {
            $bulk->addAction($action);
        }

        return $bulk;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString()
    {
        $data = '';
        foreach ($this->toArray() as $row) {
            $data.= json_encode($row) . self::DELIMITER;
        }
        return $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->getActions() as $action) {
            foreach ($action->toArray() as $row) {
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * @return \Elastica\Bulk\ResponseSet
     */
    public function send()
    {
        $path = $this->getPath();
        $data = $this->toString();

        $response = $this->_client->request($path, Request::PUT, $data);

        return $this->_processResponse($response);
    }

    /**
     * @param \Elastica\Response $response
     * @return \Elastica\Bulk\ResponseSet
     * @throws \Elastica\Exception\BulkResponseException
     */
    protected function _processResponse(Response $response)
    {
        $bulkResponseSet = new ResponseSet($response, $this->getActions());

        if ($bulkResponseSet->hasError()) {
            throw new BulkResponseException($bulkResponseSet);
        }

        return $bulkResponseSet;
    }

}