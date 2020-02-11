<?php

namespace Elastica\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;

interface BuilderInterface
{
    /**
     * Builds a ResultSet given a specific response and query.
     */
    public function buildResultSet(Response $response, Query $query): ResultSet;
}
