<?php
namespace Bonami\Elastica\Query;

use Bonami\Elastica\Exception\InvalidException;
use Bonami\Elastica\Filter\AbstractFilter;

/**
 * Filtered query. Needs a query and a filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filtered-query.html
 */
class Filtered extends AbstractQuery
{
    /**
     * Constructs a filtered query.
     *
     * @param \Bonami\Elastica\Query\AbstractQuery   $query  OPTIONAL Query object
     * @param \Bonami\Elastica\Filter\AbstractFilter $filter OPTIONAL Filter object
     */
    public function __construct(AbstractQuery $query = null, AbstractFilter $filter = null)
    {
        $this->setQuery($query);
        $this->setFilter($filter);
    }

    /**
     * Sets a query.
     *
     * @param \Bonami\Elastica\Query\AbstractQuery $query Query object
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query = null)
    {
        return $this->setParam('query', $query);
    }

    /**
     * Sets the filter.
     *
     * @param \Bonami\Elastica\Filter\AbstractFilter $filter Filter object
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter = null)
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * Gets the filter.
     *
     * @return \Bonami\Elastica\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->getParam('filter');
    }

    /**
     * Gets the query.
     *
     * @return \Bonami\Elastica\Query\AbstractQuery
     */
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * Converts query to array.
     *
     * @return array Query array
     *
     * @see \Bonami\Elastica\Query\AbstractQuery::toArray()
     */
    public function toArray()
    {
        $filtered = array();

        if ($this->hasParam('query') && $this->getParam('query') instanceof AbstractQuery) {
            $filtered['query'] = $this->getParam('query')->toArray();
        }

        if ($this->hasParam('filter') && $this->getParam('filter') instanceof AbstractFilter) {
            $filtered['filter'] = $this->getParam('filter')->toArray();
        }

        if (empty($filtered)) {
            throw new InvalidException('A query and/or filter is required');
        }

        return array('filtered' => $filtered);
    }
}
