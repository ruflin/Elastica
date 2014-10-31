<?php

namespace Elastica;

use Elastica\Exception\JSONParseException;
use Elastica\Exception\NotFoundException;
use Elastica\JSON;

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
     * HTTP response status code
     *
     * @var int
     */
    protected $_status = null;

    /**
     * Construct
     *
     * @param string|array $responseString Response string (json)
     * @param int $responseStatus http status code
     */
    public function __construct($responseString, $responseStatus = null)
    {
        if (is_array($responseString)) {
            $this->_response = $responseString;
        } else {
            $this->_responseString = $responseString;
        }
        $this->_status = $responseStatus;
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
     * True if response has failed shards
     *
     * @return bool True if response has failed shards
     */
    public function hasFailedShards()
    {
        try {
            $shardsStatistics = $this->getShardsStatistics();
        } catch (NotFoundException $e) {
            return false;
        }

        return array_key_exists('failures', $shardsStatistics);
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
        if (isset($data['status'])) {
            if ($data['status'] >= 200 && $data['status'] <= 300) {
                return true;
            }
            return false;
        }

        if (isset($data['items'])) {
            if (isset($data['errors']) && true === $data['errors']) {
                return false;
            }

            foreach ($data['items'] as $item) {
                if (isset($item['index']['ok']) && false == $item['index']['ok']) {
                    return false;

                } elseif (isset($item['index']['status']) && ($item['index']['status'] < 200 || $item['index']['status'] >= 300)) {
                    return false;
                }
            }

            return true;
        }

        if ($this->_status >= 200 && $this->_status <= 300) {
            // http status is ok
            return true;
        }

        return (isset($data['ok']) && $data['ok']);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
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
                try {
                    $response = JSON::parse($response);
                } catch (JSONParseException $e) {
                    // leave reponse as is if parse fails
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
     * @param  array $transferInfo The curl transfer information.
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
     * @param  float $queryTime Query time
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
