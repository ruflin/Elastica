<?php

namespace Elastica\Bulk;

use Elastica\Response as BaseResponse;

class ResponseSet extends BaseResponse implements \Iterator, \Countable
{
    /**
     * @var \Elastica\Bulk\Response[]
     */
    protected $_bulkResponses = array();

    /**
     * @var int
     */
    protected $_position = 0;

    /**
     * @param \Elastica\Response        $response
     * @param \Elastica\Bulk\Response[] $bulkResponses
     */
    public function __construct(BaseResponse $response, array $bulkResponses)
    {
        parent::__construct($response->getData());

        $this->_bulkResponses = $bulkResponses;
    }

    /**
     * @return \Elastica\Bulk\Response[]
     */
    public function getBulkResponses()
    {
        return $this->_bulkResponses;
    }

    /**
     * Returns first found error.
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
     * Returns first found error (full array).
     *
     * @return array|string
     */
    public function getFullError()
    {
        $error = '';

        foreach ($this->getBulkResponses() as $bulkResponse) {
            if ($bulkResponse->hasError()) {
                $error = $bulkResponse->getFullError();
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
     */
    public function next()
    {
        ++$this->_position;
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
