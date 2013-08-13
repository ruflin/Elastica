<?php

namespace Elastica\Query;
use Elastica\Filter\AbstractFilter;

/**
 * Filtered query. Needs a query and a filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/filtered-query.html
 */
class Filtered extends AbstractQuery
{
    /**
     * Query
     *
     * @var \Elastica\Query\AbstractQuery Query object
     */
    protected $_query = null;

    /**
     * Filter
     *
     * @var \Elastica\Filter\AbstractFilter Filter object
     */
    protected $_filter = null;

    /**
     * Constructs a filtered query
     *
     * @param \Elastica\Query\AbstractQuery   $query  Query object
     * @param \Elastica\Filter\AbstractFilter $filter Filter object
     */
    public function __construct(AbstractQuery $query, AbstractFilter $filter)
    {
        $this->setQuery($query);
        $this->setFilter($filter);
    }

    /**
     * Sets a query
     *
     * @param  \Elastica\Query\AbstractQuery $query Query object
     * @return \Elastica\Query\Filtered      Current object
     */
    public function setQuery(AbstractQuery $query)
    {
        $this->_query = $query;

        return $this;
    }

    /**
     * Sets the filter
     *
     * @param  \Elastica\Filter\AbstractFilter $filter Filter object
     * @return \Elastica\Query\Filtered        Current object
     */
    public function setFilter(AbstractFilter $filter)
    {
        $this->_filter = $filter;

        return $this;
    }

    /**
     * Gets the filter.
     *
     * @return \Elastica\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Gets the query.
     *
     * @return \Elastica\Query\AbstractQuery
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Converts query to array
     *
     * @return array Query array
     * @see \Elastica\Query\AbstractQuery::toArray()
     */
    public function toArray()
    {
        return array('filtered' => array(
            'query' => $this->_query->toArray(),
            'filter' => $this->_filter->toArray()
        ));
    }
}
