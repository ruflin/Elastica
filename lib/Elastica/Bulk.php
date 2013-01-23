<?php

namespace Elastica;

use Elastica\Document;
use Elastica\Exception\BulkResponseException;
use Elastica\Request;
use Elastica\Response;
use Elastica\Type;
use Elastica\Index;
use Elastica\Bulk\Action;
use Elastica\Client;

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
    public function setActions(array $actions)
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
     * @param mixed $data
     * @return $this
     */
    public function addRawAction($data)
    {
        $this->_actions[] = $data;

        return $this;
    }

    /**
     * @param \Elastica\Document $document
     * @param string $opType
     * @return $this
     */
    public function addDocument(Document $document, $opType = null)
    {
        $action = Action\AbstractDocument::create($document, $opType);

        return $this->addAction($action);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        foreach ($data as $row) {
            if ($row instanceof Document) {
                $this->addDocument($row);
            } else if ($row instanceof Action) {
                $this->addAction($row);
            } else {
                $this->addRawAction($row);
            }
        }

        return $this;
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
            if ($action instanceof Action) {
                $data[] = $action->getActionMetadata();
                if ($action->hasSource()) {
                    $data[] = $action->getSource();
                }
            } else {
                $data[] = $action;
            }
        }
        return $data;
    }

    /**
     * @return \Elastica\Response
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
     * @throws \Elastica\Exception\BulkResponseException
     * @return \Elastica\Response
     */
    protected function _processResponse(Response $response)
    {
        $data = $response->getData();

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $params = reset($item);
                if (isset($params['error'])) {
                    throw new BulkResponseException($response);
                }
            }
        }

        return $response;
    }
}