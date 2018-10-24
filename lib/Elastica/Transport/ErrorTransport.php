<?php
namespace Elastica\Transport;

use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica ErrorTransport object.
 *
 * This is used in case you just need a test transport that doesn't do any connection to an elasticsearch
 * host but still returns a valid response object
 *
 * @author Jan Domanski <jandom@gmail.com>
 */
class ErrorTransport extends AbstractTransport
{
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
        $res = [
            'message' => 'The request signature we calculated does not match the signature you provided.'
        ];

        $transferInfo  = [
            'http_code' => 403
        ];
        $response = new Response(JSON::stringify($res));
        $response->setTransferInfo($transferInfo);

        return $response;
    }
}
