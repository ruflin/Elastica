<?php

namespace Elastica\Multi;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Search as BaseSearch;

interface MultiBuilderInterface
{
    /**
     * @param BaseSearch[] $searches
     */
    public function buildMultiResultSet(Elasticsearch $response, array $searches): ResultSet;
}
