<?php

namespace Elastica\Query;

/**
 * Constant score query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-constant-score-query.html
 */
class ConstantScore extends AbstractQuery
{
    /**
     * Construct constant score query.
     *
     * @param AbstractQuery|null $filter
     */
    public function __construct(AbstractQuery $filter = null)
    {
        if (null !== $filter) {
            $this->setFilter($filter);
        }
    }

    /**
     * Set filter.
     *
     * @param AbstractQuery $filter
     *
     * @return $this
     */
    public function setFilter(AbstractQuery $filter): self
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * Set boost.
     *
     * @param float $boost
     *
     * @return $this
     */
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }
}
