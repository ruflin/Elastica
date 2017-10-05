<?php
namespace Elastica\Query;

/**
 * Constant score query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-constant-score-query.html
 */
class ConstantScore extends AbstractQuery
{
    /**
     * Construct constant score query.
     *
     * @param null|AbstractQuery|array $filter
     */
    public function __construct(AbstractQuery $filter = null)
    {
        if (!is_null($filter)) {
            $this->setFilter($filter);
        }
    }

    /**
     * Set filter.
     *
     * @param array|AbstractQuery $filter
     *
     * @return $this
     */
    public function setFilter(AbstractQuery $filter)
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
    public function setBoost($boost)
    {
        return $this->setParam('boost', $boost);
    }
}
