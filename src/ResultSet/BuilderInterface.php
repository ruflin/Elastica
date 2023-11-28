<?php

namespace Elastica\ResultSet;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Query;
use Elastica\ResultSet;

interface BuilderInterface
{
    /**
     * Builds a ResultSet given a specific response and query.
     */
    public function buildResultSet(Elasticsearch $response, Query $query): ResultSet;
}
