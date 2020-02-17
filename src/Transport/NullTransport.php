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
    protected $_response;

    /**
     * Set response object the transport returns.
     *
     * @param array $params Hostname, port, path, ...
     */
    public function getResponse(array $params = []): Response
    {
        return $this->_response ?? $this->generateDefaultResponse($params);
    }

    /**
     * Set response object the transport returns.
     *
     * @return $this
     */
    public function setResponse(Response $response): NullTransport
    {
        $this->_response = $response;

        return $this;
    }

    /**
     * Generate an example response object.
     *
     * @param array $params Hostname, port, path, ...
     *
     * @return Response $response
     */
    public function generateDefaultResponse(array $params): Response
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
                'total' => [
                    'value' => 0,
                ],
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
     * @param array $params Hostname, port, path, ...
     *
     * @return Response Response empty object
     */
    public function exec(Request $request, array $params): Response
    {
        return $this->getResponse($params);
    }
}
