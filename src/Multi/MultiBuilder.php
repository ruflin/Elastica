<?php

namespace Elastica\Multi;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\ResultSet as BaseResultSet;
use Elastica\Search as BaseSearch;
use GuzzleHttp\Psr7\Response;

class MultiBuilder implements MultiBuilderInterface
{
    /**
     * @param BaseSearch[] $searches
     */
    public function buildMultiResultSet(Elasticsearch $response, array $searches): ResultSet
    {
        $resultSets = $this->buildResultSets($response, $searches);

        return new ResultSet($response, $resultSets);
    }

    private function buildResultSet(Elasticsearch $childResponse, BaseSearch $search): BaseResultSet
    {
        return $search->getResultSetBuilder()->buildResultSet($childResponse, $search->getQuery());
    }

    /**
     * @param BaseSearch[] $searches
     *
     * @return BaseResultSet[]
     */
    private function buildResultSets(Elasticsearch $response, array $searches): array
    {
        $data = $response->asArray();
        if (!isset($data['responses']) || !\is_array($data['responses'])) {
            return [];
        }

        $resultSets = [];
        \reset($searches);

        foreach ($data['responses'] as $responseData) {
            $search = \current($searches);
            $key = \key($searches);
            \next($searches);

            // wen need any representation of response to store data in elasticsearch response
            $newResponse = new Elasticsearch();
            $newResponse->setResponse(new Response(
                200,
                [
                    Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                \json_encode($responseData)
            ));

            $resultSets[$key] = $this->buildResultSet($newResponse, $search);
        }

        return $resultSets;
    }
}
