<?php
namespace Elastica\Multi;

use Elastica\Response;
use Elastica\Search as BaseSearch;

interface MultiBuilderInterface
{
    /**
     * @param Response     $response
     * @param BaseSearch[] $searches
     *
     * @return ResultSet
     */
    public function buildMultiResultSet(Response $response, $searches);
}
