<?php
namespace Elastica\Filter;

use Elastica\Index as ElasticaIndex;

/**
 * Class Indices.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-indices-filter.html
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
     * Set the indices on which this filter should be applied.
     *
     * @param mixed[] $indices
     *
     * @return $this
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
     * Adds one more index on which this filter should be applied.
     *
     * @param string|\Elastica\Index $index
     *
     * @return $this
     */
    public function addIndex($index)
    {
        if ($index instanceof ElasticaIndex) {
            $index = $index->getName();
        }

        return $this->addParam('indices', (string) $index);
    }

    /**
     * Set the filter to be applied to docs in the specified indices.
     *
     * @param AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * Set the filter to be applied to docs in indices which do not match those specified in the "indices" parameter.
     *
     * @param AbstractFilter $filter
     *
     * @return $this
     */
    public function setNoMatchFilter(AbstractFilter $filter)
    {
        return $this->setParam('no_match_filter', $filter);
    }
}
