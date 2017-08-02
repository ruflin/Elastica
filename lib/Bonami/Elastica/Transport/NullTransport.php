<?php
namespace Bonami\Elastica\Transport;

use Bonami\Elastica\JSON;
use Bonami\Elastica\Request;
use Bonami\Elastica\Response;

/**
 * Elastica Null Transport object.
 *
 * This is used in case you just need a test transport that doesn't do any connection to an elasticsearch
 * host but still returns a valid response object
 *
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 */
class NullTransport extends AbstractTransport
{
    /**
     * Null transport.
     *
     * @param \Bonami\Elastica\Request $request
     * @param array             $params  Hostname, port, path, ...
     *
     * @return \Bonami\Elastica\Response Response empty object
     */
    public function exec(Request $request, array $params)
    {
        $response = array(
            'took' => 0,
            'timed_out' => false,
            '_shards' => array(
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
            ),
            'hits' => array(
                'total' => 0,
                'max_score' => null,
                'hits' => array(),
            ),
            'params' => $params,
        );

        return new Response(JSON::stringify($response));
    }
}
