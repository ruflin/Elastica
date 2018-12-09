<?php

namespace Elastica\Aggregation;

/**
 * Class Cardinality.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-cardinality-aggregation.html
 */
class Cardinality extends AbstractSimpleAggregation
{
    const DEFAULT_PRECISION_THRESHOLD_VALUE = 3000;

    /**
     * @param int $precisionThreshold
     *
     * @return $this
     */
    public function setPrecisionThreshold(int $precisionThreshold): self
    {
        return $this->setParam('precision_threshold', $precisionThreshold);
    }

    /**
     * @param bool $rehash
     *
     * @return $this
     */
    public function setRehash(bool $rehash): self
    {
        return $this->setParam('rehash', $rehash);
    }
}
