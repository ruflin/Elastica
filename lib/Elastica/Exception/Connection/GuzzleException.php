<?php

namespace Elastica\Exception\Connection;

use Elastica\Exception\ConnectionException;
use Elastica\Request;
use Elastica\Response;
use GuzzleHttp\Exception\GuzzleException as BaseGuzzleException;
use GuzzleHttp\Exception\TransferException;

/**
 * Transport exception.
 *
 * @author Milan Magudia <milan@magudia.com>
 */
class GuzzleException extends ConnectionException
{
    /**
     * @var TransferException
     */
    protected $_guzzleException;

    public function __construct(TransferException $guzzleException, ?Request $request = null, ?Response $response = null)
    {
        $this->_guzzleException = $guzzleException;
        $message = $this->getErrorMessage($this->getGuzzleException());
        parent::__construct($message, $request, $response);
    }

    public function getErrorMessage(TransferException $guzzleException): string
    {
        return $guzzleException->getMessage();
    }

    /**
     * @return TransferException
     */
    public function getGuzzleException(): BaseGuzzleException
    {
        return $this->_guzzleException;
    }
}
