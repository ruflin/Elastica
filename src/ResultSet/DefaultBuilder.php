<?php

namespace Elastica\ResultSet;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;

class DefaultBuilder implements BuilderInterface
{
    /**
     * Builds a ResultSet for a given Response.
     */
    public function buildResultSet(Elasticsearch $response, Query $query): ResultSet
    {
        $results = $this->buildResults($response);

        return new ResultSet($response, $query, $results);
    }

    /**
     * Builds individual result objects.
     *
     * @return Result[]
     */
    private function buildResults(Elasticsearch $response): array
    {
        $data = $response->asArray();
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
