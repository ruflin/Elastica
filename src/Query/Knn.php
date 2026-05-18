<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Param;

/**
 * Top-level kNN search.
 *
 * Note: `knn` is a sibling of `query` in the search request body, not a clause inside it.
 * Attach it via {@see \Elastica\Query::setKnn()} rather than putting it inside a BoolQuery.
 *
 * @see https://www.elastic.co/docs/solutions/search/vector/knn
 */
class Knn extends Param
{
    /**
     * @param float[] $queryVector
     */
    public function __construct(string $field, array $queryVector, int $k, int $numCandidates)
    {
        $this->setParam('field', $field);
        $this->setParam('query_vector', $queryVector);
        $this->setParam('k', $k);
        $this->setParam('num_candidates', $numCandidates);
    }

    /**
     * Adds a Query DSL filter applied before the kNN search.
     *
     * Filters are ANDed together by Elasticsearch.
     */
    public function addFilter(AbstractQuery $filter): self
    {
        return $this->addParam('filter', $filter);
    }

    /**
     * Sets the minimum similarity required for a document to be considered a match.
     */
    public function setSimilarity(float $similarity): self
    {
        return $this->setParam('similarity', $similarity);
    }

    /**
     * Boost applied to the kNN score before it is combined with other clauses.
     */
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }
}
