<?php

namespace Elastica\Query;
use Elastica\Filter\AbstractFilter;

/**
 * Constant score query
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/constant-score-query.html
 */
class ConstantScore extends AbstractQuery
{
    /**
     * Construct constant score query
     *
     * @param null|\Elastica\Filter\AbstractFilter|array $filter
     */
    public function __construct($filter = null)
    {
        if (!is_null($filter)) {
            $this->setFilter($filter);
        }
    }

    /**
     * Set filter
     *
     * @param  array|\Elastica\Filter\AbstractFilter $filter
     * @return \Elastica\Query\ConstantScore         Query object
     */
    public function setFilter($filter)
    {
        if ($filter instanceof AbstractFilter) {
            $filter = $filter->toArray();
        }

        return $this->setParam('filter', $filter);
    }

    /**
     * Set query
     *
     * @param array|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\ConstantScore         Query object
     */
    public function setQuery($query)
    {
        if ($query instanceof AbstractQuery) {
            $query = $query->toArray();
        }

        return $this->setParam('query', $query);
    }

    /**
     * Set boost
     *
     * @param  float                        $boost
     * @return \Elastica\Query\ConstantScore
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', $boost);
    }
}
