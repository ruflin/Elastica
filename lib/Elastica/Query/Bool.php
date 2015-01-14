<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Bool query
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/bool-query.html
 */
class Bool extends AbstractQuery
{
    /**
     * Add should part to query
     *
     * @param  \Elastica\Query\AbstractQuery|array $args Should query
     * @return \Elastica\Query\Bool                Current object
     */
    public function addShould($args)
    {
        return $this->_addQuery('should', $args);
    }

    /**
     * Add must part to query
     *
     * @param  \Elastica\Query\AbstractQuery|array $args Must query
     * @return \Elastica\Query\Bool                Current object
     */
    public function addMust($args)
    {
        return $this->_addQuery('must', $args);
    }

    /**
     * Add must not part to query
     *
     * @param  \Elastica\Query\AbstractQuery|array $args Must not query
     * @return \Elastica\Query\Bool                Current object
     */
    public function addMustNot($args)
    {
        return $this->_addQuery('must_not', $args);
    }

    /**
     * Adds a query to the current object
     *
     * @param  string                               $type Query type
     * @param  \Elastica\Query\AbstractQuery|array  $args Query
     * @return \Elastica\Query\Bool
     * @throws \Elastica\Exception\InvalidException If not valid query
     */
    protected function _addQuery($type, $args)
    {
        if ($args instanceof AbstractQuery) {
            $args = $args->toArray();
        }

        if (!is_array($args)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->addParam($type, $args);
    }

    /**
     * Sets boost value of this query
     *
     * @param  float                $boost Boost value
     * @return \Elastica\Query\Bool Current object
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Set the minimum number of of should match
     *
     * @param  int                  $minimumNumberShouldMatch Should match minimum
     * @return \Elastica\Query\Bool Current object
     */
    public function setMinimumNumberShouldMatch($minimumNumberShouldMatch)
    {
        return $this->setParam('minimum_number_should_match', $minimumNumberShouldMatch);
    }
}
