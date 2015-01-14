<?php

namespace Elastica\Filter;

use Elastica\Index as ElasticaIndex;

/**
 * Class Indices
 * @package Elastica\Filter
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-indices-filter.html
 */
class Indices extends AbstractFilter
{
    /**
     * @param AbstractFilter $filter  filter which will be applied to docs in the specified indices
     * @param mixed[]        $indices
     */
    public function __construct(AbstractFilter $filter, array $indices)
    {
        $this->setIndices($indices)->setFilter($filter);
    }

    /**
     * Set the indices on which this filter should be applied
     * @param  mixed[] $indices
     * @return Indices
     */
    public function setIndices(array $indices)
    {
        $this->setParam('indices', array());
        foreach ($indices as $index) {
            $this->addIndex($index);
        }

        return $this;
    }

    /**
     * Adds one more index on which this filter should be applied
     * @param  string|\Elastica\Index $index
     * @return Indices
     */
    public function addIndex($index)
    {
        if ($index instanceof ElasticaIndex) {
            $index = $index->getName();
        }

        return $this->addParam('indices', (string) $index);
    }

    /**
     * Set the filter to be applied to docs in the specified indices
     * @param  AbstractFilter $filter
     * @return Indices
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * Set the filter to be applied to docs in indices which do not match those specified in the "indices" parameter
     * @param  AbstractFilter $filter
     * @return Indices
     */
    public function setNoMatchFilter(AbstractFilter $filter)
    {
        return $this->setParam('no_match_filter', $filter->toArray());
    }
}
