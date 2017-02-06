<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

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
     * Set query.
     *
     * @param array|AbstractQuery $query
     *
     * @throws InvalidException If query is not an array or instance of AbstractQuery
     *
     * @return $this
     */
    public function setQuery($query)
    {
        if (!is_array($query) && !($query instanceof AbstractQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->setParam('query', $query);
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
