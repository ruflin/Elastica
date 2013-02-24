<?php

namespace Elastica\Transport;

use Elastica\Request;
use Elastica\Response;

/**
 * Elastica Null Transport object
 *
 * @package Elastica
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 */
class Null extends AbstractTransport
{
    /**
     * Null transport.
     *
     * @param \Elastica\Request $request
     * @param  array             $params Hostname, port, path, ...
     * @return \Elastica\Response Response empty object
     */
    public function exec(Request $request, array $params)
    {
        $response = array(
                "took" => 0,
                "timed_out" => FALSE,
                "_shards" => array(
                        "total" => 0,
                        "successful" => 0,
                        "failed" => 0
                        ),
                "hits" => array(
                        "total" => 0,
                        "max_score" => NULL,
                        "hits" => array()
                        ),
                "params" => $params
                );

         return new Response(json_encode($response));
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return 'null://';
    }
}
