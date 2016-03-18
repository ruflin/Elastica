<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;

/**
 * Bool query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 */
class BoolQuery extends AbstractQuery
{
    /**
     * Add should part to query.
     *
     * @param \Elastica\Query\AbstractQuery|array $args Should query
     *
     * @return $this
     */
    public function addShould($args)
    {
        return $this->_addQuery('should', $args);
    }

    /**
     * Add must part to query.
     *
     * @param \Elastica\Query\AbstractQuery|array $args Must query
     *
     * @return $this
     */
    public function addMust($args)
    {
        return $this->_addQuery('must', $args);
    }

    /**
     * Add must not part to query.
     *
     * @param \Elastica\Query\AbstractQuery|array $args Must not query
     *
     * @return $this
     */
    public function addMustNot($args)
    {
        return $this->_addQuery('must_not', $args);
    }

    /**
     * Sets the filter.
     *
     * @param \Elastica\Query\AbstractQuery $filter Filter object
     *
     * @return $this
     */
    public function addFilter($filter)
    {
        if ($filter instanceof AbstractFilter) {
            trigger_error('Deprecated: Elastica\Query\BoolQuery::addFilter passing AbstractFilter is deprecated. Pass AbstractQuery instead.', E_USER_DEPRECATED);
        } elseif (!($filter instanceof AbstractQuery)) {
            throw new InvalidException('Filter must be instance of AbstractQuery');
        }

        return $this->addParam('filter', $filter);
    }

    /**
     * Adds a query to the current object.
     *
     * @param string                              $type Query type
     * @param \Elastica\Query\AbstractQuery|array $args Query
     *
     * @throws \Elastica\Exception\InvalidException If not valid query
     *
     * @return $this
     */
    protected function _addQuery($type, $args)
    {
        if (!is_array($args) && !($args instanceof AbstractQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->addParam($type, $args);
    }

    /**
     * Sets boost value of this query.
     *
     * @param float $boost Boost value
     *
     * @return $this
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Set the minimum number of of should match.
     *
     * @param int $minimumNumberShouldMatch Should match minimum
     *
     * @return $this
     */
    public function setMinimumNumberShouldMatch($minimumNumberShouldMatch)
    {
        return $this->setParam('minimum_number_should_match', $minimumNumberShouldMatch);
    }

    /**
     * Converts array to an object in case no queries are added.
     *
     * @return array
     */
    public function toArray()
    {
        if (empty($this->_params)) {
            $this->_params = new \stdClass();
        }

        return parent::toArray();
    }
}
