<?php

namespace Elastica\Exception\Connection;

use Elastica\Exception\ConnectionException;
use Elastica\Request;
use Elastica\Response;

/**
 * Connection exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class HttpException extends ConnectionException
{
    /**
     * Error code / message.
     *
     * @var int Error code / message
     */
    protected $_error = 0;

    /**
     * Construct Exception.
     *
     * @param int      $error
     * @param Request  $request
     * @param Response $response
     */
    public function __construct($error, ?Request $request = null, ?Response $response = null)
    {
        $this->_error = $error;

        $message = $this->getErrorMessage($this->getError());
        parent::__construct($message, $request, $response);
    }

    /**
     * Returns the error message corresponding to the error code
     * cUrl error code reference can be found here {@link http://curl.haxx.se/libcurl/c/libcurl-errors.html}.
     *
     * @param int $error Error code
     *
     * @return string Error message
     */
    public function getErrorMessage(int $error): string
    {
        switch ($error) {
            case \CURLE_UNSUPPORTED_PROTOCOL:
                return 'Unsupported protocol';
            case \CURLE_FAILED_INIT:
                return 'Internal cUrl error?';
            case \CURLE_URL_MALFORMAT:
                return 'Malformed URL';
            case \CURLE_COULDNT_RESOLVE_PROXY:
                return "Couldn't resolve proxy";
            case \CURLE_COULDNT_RESOLVE_HOST:
                return "Couldn't resolve host";
            case \CURLE_COULDNT_CONNECT:
                return "Couldn't connect to host, Elasticsearch down?";
            case \CURLE_OPERATION_TIMEOUTED:
                return 'Operation timed out';
        }

        return 'Unknown error:'.$error;
    }

    /**
     * Return Error code / message.
     *
     * @return int Error code / message
     */
    public function getError(): int
    {
        return $this->_error;
    }
}
