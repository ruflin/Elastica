<?php

namespace Elastica;

use Elastica\Exception\JSONParseException;
use Elastica\Exception\NotFoundException;

/**
 * Elastica Response object.
 *
 * Stores query time, and result array -> is given to result set, returned by ...
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Response
{
    /**
     * Query time.
     *
     * @var float Query time
     */
    protected $_queryTime;

    /**
     * Response string (json).
     *
     * @var string Response
     */
    protected $_responseString = '';

    /**
     * Transfer info.
     *
     * @var array transfer info
     */
    protected $_transferInfo = [];

    /**
     * Response.
     *
     * @var array|null
     */
    protected $_response;

    /**
     * HTTP response status code.
     *
     * @var int
     */
    protected $_status;

    /**
     * Whether or not to convert bigint results to string (see issue #717).
     *
     * @var bool
     */
    protected $_jsonBigintConversion = false;

    /**
     * Construct.
     *
     * @param array|string $responseString Response string (json)
     * @param int          $responseStatus http status code
     */
    public function __construct($responseString, $responseStatus = null)
    {
        if (\is_array($responseString)) {
            $this->_response = $responseString;
        } else {
            $this->_responseString = $responseString;
        }
        $this->_status = $responseStatus;
    }

    /**
     * Error message.
     *
     * @return string Error message
     */
    public function getError()
    {
        $error = $this->getFullError();

        if (!$error) {
            return '';
        }

        if (\is_string($error)) {
            return $error;
        }

        $rootError = $error;
        if (isset($error['root_cause'][0])) {
            $rootError = $error['root_cause'][0];
        }

        $message = $rootError['reason'];
        if (isset($rootError['index'])) {
            $message .= ' [index: '.$rootError['index'].']';
        }

        if (isset($error['reason']) && $rootError['reason'] != $error['reason']) {
            $message .= ' [reason: '.$error['reason'].']';
        }

        return $message;
    }

    /**
     * A keyed array representing any errors that occurred.
     *
     * In case of http://localhost:9200/_alias/test the error is a string
     *
     * @return array|string|null Error data or null if there is no error
     */
    public function getFullError()
    {
        $response = $this->getData();

        if (isset($response['error'])) {
            return $response['error'];
        }
    }

    /**
     * @return string Error string based on the error object
     */
    public function getErrorMessage()
    {
        return $this->getError();
    }

    /**
     * True if response has error.
     *
     * @return bool True if response has error
     */
    public function hasError()
    {
        $response = $this->getData();

        return isset($response['error']);
    }

    /**
     * True if response has failed shards.
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

        return \array_key_exists('failures', $shardsStatistics);
    }

    /**
     * Checks if the query returned ok.
     *
     * @return bool True if ok
     */
    public function isOk()
    {
        $data = $this->getData();

        // Bulk insert checks. Check every item
        if (isset($data['status'])) {
            return $data['status'] >= 200 && $data['status'] <= 300;
        }

        if (isset($data['items'])) {
            if (isset($data['errors']) && true === $data['errors']) {
                return false;
            }

            foreach ($data['items'] as $item) {
                if (isset($item['index']['ok']) && false == $item['index']['ok']) {
                    return false;
                }

                if (isset($item['index']['status']) && ($item['index']['status'] < 200 || $item['index']['status'] >= 300)) {
                    return false;
                }
            }

            return true;
        }

        if ($this->_status >= 200 && $this->_status <= 300) {
            // http status is ok
            return true;
        }

        return isset($data['ok']) && $data['ok'];
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Response data array.
     *
     * @return array Response data array
     */
    public function getData()
    {
        if (null == $this->_response) {
            $response = $this->_responseString;

            try {
                if ($this->getJsonBigintConversion()) {
                    $response = JSON::parse($response, true, 512, JSON_BIGINT_AS_STRING);
                } else {
                    $response = JSON::parse($response);
                }
            } catch (JSONParseException $e) {
                // leave response as is if parse fails
            }

            if (empty($response)) {
                $response = [];
            }

            if (\is_string($response)) {
                $response = ['message' => $response];
            }

            $this->_response = $response;
            $this->_responseString = '';
        }

        return $this->_response;
    }

    /**
     * Gets the transfer information.
     *
     * @return array information about the curl request
     */
    public function getTransferInfo()
    {
        return $this->_transferInfo;
    }

    /**
     * Sets the transfer info of the curl request. This function is called
     * from the \Elastica\Client::_callService .
     *
     * @param array $transferInfo the curl transfer information
     *
     * @return $this
     */
    public function setTransferInfo(array $transferInfo)
    {
        $this->_transferInfo = $transferInfo;

        return $this;
    }

    /**
     * Returns query execution time.
     *
     * @return float Query time
     */
    public function getQueryTime()
    {
        return $this->_queryTime;
    }

    /**
     * Sets the query time.
     *
     * @param float $queryTime Query time
     *
     * @return $this
     */
    public function setQueryTime($queryTime)
    {
        $this->_queryTime = $queryTime;

        return $this;
    }

    /**
     * Time request took.
     *
     * @throws \Elastica\Exception\NotFoundException
     *
     * @return int Time request took
     */
    public function getEngineTime()
    {
        $data = $this->getData();

        if (!isset($data['took'])) {
            throw new NotFoundException('Unable to find the field [took]from the response');
        }

        return $data['took'];
    }

    /**
     * Get the _shard statistics for the response.
     *
     * @throws \Elastica\Exception\NotFoundException
     *
     * @return array
     */
    public function getShardsStatistics()
    {
        $data = $this->getData();

        if (!isset($data['_shards'])) {
            throw new NotFoundException('Unable to find the field [_shards] from the response');
        }

        return $data['_shards'];
    }

    /**
     * Get the _scroll value for the response.
     *
     * @throws \Elastica\Exception\NotFoundException
     *
     * @return string
     */
    public function getScrollId()
    {
        $data = $this->getData();

        if (!isset($data['_scroll_id'])) {
            throw new NotFoundException('Unable to find the field [_scroll_id] from the response');
        }

        return $data['_scroll_id'];
    }

    /**
     * Sets whether or not to apply bigint conversion on the JSON result.
     *
     * @param bool $jsonBigintConversion
     */
    public function setJsonBigintConversion($jsonBigintConversion): void
    {
        $this->_jsonBigintConversion = $jsonBigintConversion;
    }

    /**
     * Gets whether or not to apply bigint conversion on the JSON result.
     *
     * @return bool
     */
    public function getJsonBigintConversion()
    {
        return $this->_jsonBigintConversion;
    }
}
