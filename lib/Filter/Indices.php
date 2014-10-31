<?php

namespace Elastica\Filter;


/**
 * Class Indices
 * @package Elastica\Filter
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/0.90/query-dsl-indices-filter.html
 */
class Indices extends AbstractFilter
{
    /**
     * @param AbstractFilter $filter filter which will be applied to docs in the specified indices
     * @param string[] $indices
     */
    public function __construct(AbstractFilter $filter, array $indices)
    {
        $this->setIndices($indices)->setFilter($filter);
    }

    /**
     * Set the names of the indices on which this filter should be applied
     * @param string[] $indices
     * @return Indices
     */
    public function setIndices(array $indices)
    {
        return $this->setParam('indices', $indices);
    }

    /**
     * Set the filter to be applied to docs in the specified indices
     * @param AbstractFilter $filter
     * @return Indices
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * Set the filter to be applied to docs in indices which do not match those specified in the "indices" parameter
     * @param AbstractFilter $filter
     * @return Indices
     */
    public function setNoMatchFilter(AbstractFilter $filter)
    {
        return $this->setParam('no_match_filter', $filter->toArray());
    }
} 