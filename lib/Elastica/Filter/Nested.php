<?php

namespace Elastica\Filter;

use Elastica\Query\AbstractQuery;

/**
 * Nested filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/nested-filter.html
 */
class Nested extends AbstractFilter
{
    /**
     * Adds field to mlt filter
     *
     * @param  string                  $path Nested object path
     * @return \Elastica\Filter\Nested
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * Sets nested query
     *
     * @param  \Elastica\Query\AbstractQuery $query
     * @return \Elastica\Filter\Nested
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query->toArray());
    }

    /**
     * Sets nested filter
     *
     * @param  \Elastica\Filter\AbstractFilter $filter
     * @return \Elastica\Filter\Nested
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * Set join option
     *
     * @param  bool                    $join
     * @return \Elastica\Filter\Nested
     */
    public function setJoin($join)
    {
        return $this->setParam('join', (bool) $join);
    }
}
