<?php

namespace Elastica\Multi;

use Elastica\Response;
use Elastica\ResultSet as BaseResultSet;
use Elastica\Search as BaseSearch;

class MultiBuilder implements MultiBuilderInterface
{
    /**
     * @param BaseSearch[] $searches
     */
    public function buildMultiResultSet(Response $response, array $searches): ResultSet
    {
        $resultSets = $this->buildResultSets($response, $searches);

        return new ResultSet($response, $resultSets);
    }

    private function buildResultSet(Response $childResponse, BaseSearch $search): BaseResultSet
    {
        return $search->getResultSetBuilder()->buildResultSet($childResponse, $search->getQuery());
    }

    /**
     * @param BaseSearch[] $searches
     *
     * @return BaseResultSet[]
     */
    private function buildResultSets(Response $response, $searches): array
    {
        $data = $response->getData();
        if (!isset($data['responses']) || !\is_array($data['responses'])) {
            return [];
        }

        $resultSets = [];
        \reset($searches);

        foreach ($data['responses'] as $responseData) {
            $search = \current($searches);
            $key = \key($searches);
            \next($searches);

            $resultSets[$key] = $this->buildResultSet(new Response($responseData), $search);
        }

        return $resultSets;
    }
}
