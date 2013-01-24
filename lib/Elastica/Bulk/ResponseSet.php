<?php

namespace Elastica\Bulk;

use Elastica\Exception\InvalidException;
use Elastica\Response;
use Elastica\Bulk\Response as BulkResponse;

class ResponseSet extends Response implements \Iterator, \Countable
{
    /**
     * @var BulkResponse[]
     */
    protected $_bulkResponses = array();

    /**
     * @var int
     */
    protected $_position = 0;

    /**
     * @param \Elastica\Response $response
     * @param Action[] $actions
     */
    public function __construct(Response $response, array $actions)
    {
        parent::__construct($response->getData());
        $this->_init($actions);
    }

    /**
     * @param Action[] $actions
     * @throws \Elastica\Exception\InvalidException
     */
    protected function _init(array $actions)
    {
        $responseData = $this->getData();

        if (isset($responseData['items']) && is_array($responseData['items'])) {
            foreach ($responseData['items'] as $key => $item) {

                if (!isset($actions[$key])) {
                    throw new InvalidException('No response found for action #' . $key);
                } elseif (!$actions[$key] instanceof Action) {
                    throw new InvalidException('Invalid object for response #' . $key . ' provided. Should be Elastica\Bulk\Action');
                }

                $opType = key($item);
                $bulkResponseData = reset($item);

                $this->_bulkResponses[] = new BulkResponse($bulkResponseData, $actions[$key], $opType);
            }
        }
    }

    /**
     * @return BulkResponse[]
     */
    public function getBulkResponses()
    {
        return $this->_bulkResponses;
    }

    /**
     * Returns first found error
     *
     * @return string
     */
    public function getError()
    {
        $error = '';

        foreach ($this->getBulkResponses() as $bulkResponse) {
            if ($bulkResponse->hasError()) {
                $error = $bulkResponse->getError();
                break;
            }
        }

        return $error;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        $return = true;

        foreach ($this->getBulkResponses() as $bulkResponse) {
            if (!$bulkResponse->isOk()) {
                $return = false;
                break;
            }
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        $return = false;
        foreach ($this->getBulkResponses() as $bulkResponse) {
            if ($bulkResponse->hasError()) {
                $return = true;
                break;
            }
        }

        return $return;
    }

    /**
     * @return bool|\Elastica\Bulk\Response
     */
    public function current()
    {
        if ($this->valid()) {
            return $this->_bulkResponses[$this->key()];
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->_bulkResponses[$this->key()]);
    }

    /**
     *
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_bulkResponses);
    }
}
