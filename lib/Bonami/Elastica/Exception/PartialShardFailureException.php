<?php
namespace Bonami\Elastica\Exception;

use Bonami\Elastica\JSON;
use Bonami\Elastica\Request;
use Bonami\Elastica\Response;

/**
 * Partial shard failure exception.
 *
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class PartialShardFailureException extends ResponseException
{
    /**
     * Construct Exception.
     *
     * @param \Bonami\Elastica\Request  $request
     * @param \Bonami\Elastica\Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $shardsStatistics = $response->getShardsStatistics();
        $this->message = JSON::stringify($shardsStatistics['failed']);
    }
}
