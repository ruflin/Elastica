<?php
/**
 * Client exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Exception_Client extends Elastica_Exception_Abstract
{
    /**
     * Error code / message
     *
     * @var string Error code / message
     */
    protected $_error = 0;

    /**
     * Request
     *
     * @var Elastica_Request Request object
     */
    protected $_request = null;

    /**
     * Response
     *
     * @var Elastica_Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception
     *
     * @param string            $error    Error
     * @param Elastica_Request  $request
     * @param Elastica_Response $response
     */
    public function __construct($error, Elastica_Request $request = null, Elastica_Response $response = null)
    {
        $this->_error = $error;
        $this->_request = $request;
        $this->_response = $response;

        $message = $this->getErrorMessage($this->getError());
        parent::__construct($message);
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

    /**
     * Returns request object
     *
     * @return Elastica_Transport_Abstract Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object
     *
     * @return Elastica_Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
