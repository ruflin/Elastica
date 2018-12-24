<?php

namespace Elastica\Transport;

use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica Null Transport object.
 *
 * This is used in case you just need a test transport that doesn't do any connection to an elasticsearch
 * host but still returns a valid response object
 *
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 * @author Jan Domanski <jandom@gmail.com>
 */
class NullTransport extends AbstractTransport
{
    /**
     * Response you want to get from the transport.
     *
     * @var Response Response
     */
    protected $_response = null;

    /**
     * Set response object the transport returns.
     *
     * @param \Elastica\Response $response
     *
     * @return $this
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set response object the transport returns.
     *
     * @param \Elastica\Response $response
     *
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->_response = $response;

        return $this->_response;
    }

    /**
     * Generate an example response object.
     *
     * @param array $params Hostname, port, path, ...
     *
     * @return \Elastica\Response $response
     */
    public function generateDefaultResponse(array $params)
    {
        $response = [
            'took' => 0,
            'timed_out' => false,
            '_shards' => [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
            ],
            'hits' => [
                'total' => 0,
                'max_score' => null,
                'hits' => [],
            ],
            'params' => $params,
        ];

        return new Response(JSON::stringify($response));
    }

    /**
     * Null transport.
     *
     * @param \Elastica\Request $request
     * @param array             $params  Hostname, port, path, ...
     *
     * @return \Elastica\Response Response empty object
     */
    public function exec(Request $request, array $params)
    {
        $response = $this->getResponse();

        if (!$response) {
            $response = $this->generateDefaultResponse($params);
        }

        return $response;
    }
}
