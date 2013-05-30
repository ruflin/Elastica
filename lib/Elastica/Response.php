<?php

namespace Elastica;
use Elastica\Exception\NotFoundException;

/**
 * Elastica Response object
 *
 * Stores query time, and result array -> is given to result set, returned by ...
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Response
{
    /**
     * Query time
     *
     * @var float Query time
     */
    protected $_queryTime = null;

    /**
     * Response string (json)
     *
     * @var string Response
     */
    protected $_responseString = '';

    /**
     * Error
     *
     * @var boolean Error
     */
    protected $_error = false;

    /**
     * Transfer info
     *
     * @var array transfer info
     */
    protected $_transferInfo = array();

    /**
     * Response
     *
     * @var \Elastica\Response Response object
     */
    protected $_response = null;

    /**
     * Construct
     *
     * @param string|array $responseString Response string (json)
     */
    public function __construct($responseString)
    {
        if (is_array($responseString)) {
            $this->_response = $responseString;
        } else {
            $this->_responseString = $responseString;
        }
    }

    /**
     * Error message
     *
     * @return string Error message
     */
    public function getError()
    {
        $message = '';
        $response = $this->getData();

        if (isset($response['error'])) {
            $message = $response['error'];
        }

        return $message;
    }

    /**
     * True if response has error
     *
     * @return bool True if response has error
     */
    public function hasError()
    {
        $response = $this->getData();

        if (isset($response['error'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the query returned ok
     *
     * @return bool True if ok
     */
    public function isOk()
    {
        $data = $this->getData();

        // Bulk insert checks. Check every item
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                if (false == $item['index']['ok']) {
                    return false;
                 }
            }

            return true;
        }

        return (isset($data['ok']) && $data['ok']);
    }

    /**
     * Response data array
     *
     * @return array Response data array
     */
    public function getData()
    {
        if ($this->_response == null) {
            $response = $this->_responseString;
            if ($response === false) {
                $this->_error = true;
            } else {

                $tempResponse = json_decode($response, true);
                // If error is returned, json_decode makes empty string of string
                if (!empty($tempResponse)) {
                    $response = $tempResponse;
                }
            }

            if (empty($response)) {
                $response = array();
            }

            if (is_string($response)) {
                $response = array('message' => $response);
            }

            $this->_response = $response;
        }

        return $this->_response;
    }

    /**
     * Gets the transfer information.
     *
     * @return array Information about the curl request.
     */
    public function getTransferInfo()
    {
        return $this->_transferInfo;
    }

    /**
     * Sets the transfer info of the curl request. This function is called
     * from the \Elastica\Client::_callService .
     *
     * @param  array             $transferInfo The curl transfer information.
     * @return \Elastica\Response Current object
     */
    public function setTransferInfo(array $transferInfo)
    {
        $this->_transferInfo = $transferInfo;
        return $this;
    }

    /**
     * This is only available if DEBUG constant is set to true
     *
     * @return float Query time
     */
    public function getQueryTime()
    {
        return $this->_queryTime;
    }

    /**
     * Sets the query time
     *
     * @param  float             $queryTime Query time
     * @return \Elastica\Response Current object
     */
    public function setQueryTime($queryTime)
    {
        $this->_queryTime = $queryTime;

        return $this;
    }

    /**
     * Time request took
     *
     * @throws \Elastica\Exception\NotFoundException
     * @return int                                  Time request took
     */
    public function getEngineTime()
    {
        $data = $this->getData();

        if (!isset($data['took'])) {
            throw new NotFoundException("Unable to find the field [took]from the response");
        }

        return $data['took'];
    }

    /**
     * Get the _shard statistics for the response
     *
     * @throws \Elastica\Exception\NotFoundException
     * @return array
     */
    public function getShardsStatistics()
    {
        $data = $this->getData();

        if (!isset($data['_shards'])) {
            throw new NotFoundException("Unable to find the field [_shards] from the response");
        }

        return $data['_shards'];
    }

    /**
     * Get the _scroll value for the response
     *
     * @throws \Elastica\Exception\NotFoundException
     * @return string
     */
    public function getScrollId()
    {
        $data = $this->getData();

        if (!isset($data['_scroll_id'])) {
            throw new NotFoundException("Unable to find the field [_scroll_id] from the response");
        }

        return $data['_scroll_id'];
    }
}
