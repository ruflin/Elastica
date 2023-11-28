<?php

namespace Elastica\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\Result;
use Elastica\ResultSet;

class DefaultBuilder implements BuilderInterface
{
    /**
     * Builds a ResultSet for a given Response.
     */
    public function buildResultSet(Response $response, Query $query): ResultSet
    {
        $results = $this->buildResults($response);

        return new ResultSet($response, $query, $results);
    }

    /**
     * Builds individual result objects.
     *
     * @return Result[]
     */
    private function buildResults(Response $response): array
    {
        $data = $response->getData();
        $results = [];

        if (!isset($data['hits']['hits'])) {
            return $results;
        }

        foreach ($data['hits']['hits'] as $hit) {
            $results[] = new Result($hit);
        }

        return $results;
    }
}
