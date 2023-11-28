<?php

namespace Elastica\Multi;

use Elastica\Response;
use Elastica\Search as BaseSearch;

interface MultiBuilderInterface
{
    /**
     * @param BaseSearch[] $searches
     */
    public function buildMultiResultSet(Response $response, array $searches): ResultSet;
}
