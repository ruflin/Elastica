<?php
/**
 * Elastica Response object
 *
 * Stores query time, and result array -> is given to resultset, returned by ...
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Response
{
    /**
     * Creates an Elastica_Response object from response data.
     *
     * @param string|array $responseData A json response string or response data array.
     * @return Elastica_Response
     * @throws Elastica_Exception_Invalid If the response is empty.
     */
    public static function create($responseData)
    {
        $response = array();

        if (is_array($responseData)) {
            $response = $responseData;
        }
        elseif (is_string($responseData)) {
            $response = json_decode($responseData, true);

            if (!is_array($response)) {
                $response = array('message' => $response);
            }
        }

        if (empty($response)) {
            throw new Elastica_Exception_Invalid('Response is data is invalid.');
        }

        return new self($response);
    }

    /**
     * Query time
     *
     * @var float Query time
     */
    protected $_queryTime = null;

    /**
     * Transfer info
     *
     * @var array transfer info
     */
    protected $_transferInfo = array();

    /**
     * Response data
     *
     * @var array
     */
    protected $_response = array();

    /**
     * Construct
     *
     * @param array $response Response data
     */
    public function __construct($response = array())
    {
        $this->_response = $response;
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

        return (isset($data['ok']) && $data['ok']);
    }

    /**
     * Response data array
     *
     * @return array Response data array
     */
    public function getData()
    {
       return $this->_response;
    }

    /**
     * Gets the transfer information if in DEBUG mode.
     *
     * @return array Information about the curl request.
     */
    public function getTransferInfo()
    {
        return $this->_transferInfo;
    }

    /**
     * Sets the transfer info of the curl request. This function is called
     * from the Elastica_Client::_callService only in debug mode.
     *
     * @param  array             $transferInfo The curl transfer information.
     * @return Elastica_Response Current object
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
     * @return Elastica_Response Current object
     */
    public function setQueryTime($queryTime)
    {
        $this->_queryTime = $queryTime;

        return $this;
    }

    /**
     * Time request took
     *
     * @return int Time request took
     */
    public function getEngineTime()
    {
        $data = $this->getData();

        if (!isset($data['took'])) {
            throw new Elastica_Exception_NotFound("Unable to find the field [took] from the response");
        }

        return $data['took'];
    }

    /**
     * Get the _shard statistics for the response
     *
     * @return array
     */
    public function getShardsStatistics()
    {
        $data = $this->getData();

        if (!isset($data['_shards'])) {
            throw new Elastica_Exception_NotFound("Unable to find the field [_shards] from the response");
        }

        return $data['_shards'];
    }
}
