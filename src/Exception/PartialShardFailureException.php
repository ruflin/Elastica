<?php

namespace Elastica\Exception;

use Elastica\Request;
use Elastica\Response;

/**
 * Partial shard failure exception.
 *
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class PartialShardFailureException extends ResponseException
{
    /**
     * Construct Exception.
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $shardsStatistics = $response->getShardsStatistics();
        $this->message = \json_encode($shardsStatistics, \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR);
    }
}
