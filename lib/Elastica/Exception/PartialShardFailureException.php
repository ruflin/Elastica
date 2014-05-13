<?php

namespace Elastica\Exception;

use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;

/**
 * Partial shard failure exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class PartialShardFailureException extends ResponseException
{

    /**
     * Construct Exception
     *
     * @param \Elastica\Request $request
     * @param \Elastica\Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $shardsStatistics = $response->getShardsStatistics();
        $this->message = JSON::stringify($shardsStatistics['failed']);
    }

}
