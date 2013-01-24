<?php

namespace Elastica\Exception\Connection;

use Elastica\Exception\ConnectionException;
use Elastica\Request;
use Elastica\Response;

/**
 * Connection exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class HttpException extends ConnectionException
{
    /**
     * Error code / message
     *
     * @var string Error code / message
     */
    protected $_error = 0;

    /**
     * Construct Exception
     *
     * @param string            $error    Error
     * @param \Elastica\Request  $request
     * @param \Elastica\Response $response
     */
    public function __construct($error, Request $request = null, Response $response = null)
    {
        $this->_error = $error;

        $message = $this->getErrorMessage($this->getError());
        parent::__construct($message, $request, $response);
    }

    /**
     * Returns the error message corresponding to the error code
     * cUrl error code reference can be found here {@link http://curl.haxx.se/libcurl/c/libcurl-errors.html}
     *
     * @param  string $error Error code
     * @return string Error message
     */
    public function getErrorMessage($error)
    {
        switch ($error) {
            case CURLE_UNSUPPORTED_PROTOCOL:
                $error = "Unsupported protocol";
                break;
            case CURLE_FAILED_INIT:
                $error = "Internal cUrl error?";
                break;
            case CURLE_URL_MALFORMAT:
                $error = "Malformed URL";
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
                $error = "Couldn't resolve proxy";
                break;
            case CURLE_COULDNT_RESOLVE_HOST:
                $error = "Couldn't resolve host";
                break;
            case CURLE_COULDNT_CONNECT:
                $error = "Couldn't connect to host, ElasticSearch down?";
                break;
            case 28:
                $error = "Operation timed out";
                break;
            default:
                $error = "Unknown error:" . $error;
                break;
        }

        return $error;
    }

    /**
     * Return Error code / message
     *
     * @return string Error code / message
     */
    public function getError()
    {
        return $this->_error;
    }
}
