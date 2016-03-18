<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;

trigger_error('Use BoolQuery instead. Filtered query is deprecated since ES 2.0.0-beta1 and this class will be removed in further Elastica releases.', E_USER_DEPRECATED);

/**
 * Filtered query. Needs a query and a filter.
 *
 * @deprecated Use BoolQuery instead. Filtered query is deprecated since ES 2.0.0-beta1 and this class will be removed in further Elastica releases.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filtered-query.html
 */
class Filtered extends AbstractQuery
{
    /**
     * Constructs a filtered query.
     *
     * @param \Elastica\Query\AbstractQuery $query  OPTIONAL Query object
     * @param \Elastica\Query\AbstractQuery $filter OPTIONAL Filter object
     */
    public function __construct(AbstractQuery $query = null, $filter = null)
    {
        $this->setQuery($query);

        if (null !== $filter) {
            if ($filter instanceof AbstractFilter) {
                trigger_error('Deprecated: Elastica\Query\Filtered passing AbstractFilter is deprecated. Pass AbstractQuery instead.', E_USER_DEPRECATED);
            } elseif (!($filter instanceof AbstractQuery)) {
                throw new InvalidException('Filter must be instance of AbstractQuery');
            }
        }

        $this->setFilter($filter);
    }

    /**
     * Sets a query.
     *
     * @param \Elastica\Query\AbstractQuery $query Query object
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
     * @param \Elastica\Query\AbstractQuery $filter Filter object
     *
     * @return $this
     */
    public function setFilter($filter = null)
    {
        if (null !== $filter) {
            if ($filter instanceof AbstractFilter) {
                trigger_error('Deprecated: Elastica\Query\Filtered::setFilter passing AbstractFilter is deprecated. Pass AbstractQuery instead.', E_USER_DEPRECATED);
            } elseif (!($filter instanceof AbstractQuery)) {
                throw new InvalidException('Filter must be instance of AbstractQuery');
            }
        }

        return $this->setParam('filter', $filter);
    }

    /**
     * Gets the filter.
     *
     * @return \Elastica\Query\AbstractQuery|\Elastica\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->getParam('filter');
    }

    /**
     * Gets the query.
     *
     * @return \Elastica\Query\AbstractQuery
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
     * @see \Elastica\Query\AbstractQuery::toArray()
     */
    public function toArray()
    {
        $filtered = array();

        if ($this->hasParam('query') && $this->getParam('query') instanceof AbstractQuery) {
            $filtered['query'] = $this->getParam('query')->toArray();
        }

        if ($this->hasParam('filter') && ($this->getParam('filter') instanceof AbstractQuery || $this->getParam('filter') instanceof AbstractFilter)) {
            $filtered['filter'] = $this->getParam('filter')->toArray();
        }

        if (empty($filtered)) {
            throw new InvalidException('A query and/or filter is required');
        }

        return array('filtered' => $filtered);
    }
}
