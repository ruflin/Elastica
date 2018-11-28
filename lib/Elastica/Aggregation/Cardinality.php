<?php
namespace Elastica\Aggregation;

/**
 * Class Cardinality.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-cardinality-aggregation.html
 */
class Cardinality extends AbstractSimpleAggregation
{
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
        if (!is_bool($rehash)) {
            throw new \InvalidArgumentException('rehash only supports boolean values');
        }

        return $this->setParam('rehash', $rehash);
    }
}
