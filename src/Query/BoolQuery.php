<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Bool query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 */
class BoolQuery extends AbstractQuery
{
    /**
     * Add should part to query.
     *
     * @param AbstractQuery|array $args Should query
     *
     * @return $this
     */
    public function addShould($args): self
    {
        return $this->_addQuery('should', $args);
    }

    /**
     * Add must part to query.
     *
     * @param AbstractQuery|array $args Must query
     *
     * @return $this
     */
    public function addMust($args): self
    {
        return $this->_addQuery('must', $args);
    }

    /**
     * Add must not part to query.
     *
     * @param AbstractQuery|array $args Must not query
     *
     * @return $this
     */
    public function addMustNot($args): self
    {
        return $this->_addQuery('must_not', $args);
    }

    /**
     * Sets the filter.
     *
     * @return $this
     */
    public function addFilter(AbstractQuery $filter): self
    {
        return $this->addParam('filter', $filter);
    }

    /**
     * Sets boost value of this query.
     *
     * @param float $boost Boost value
     *
     * @return $this
     */
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Sets the minimum number of should clauses to match.
     *
     * @param int|string $minimum Minimum value
     *
     * @return $this
     */
    public function setMinimumShouldMatch($minimum): self
    {
        return $this->setParam('minimum_should_match', $minimum);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (empty($this->_params)) {
            $this->_params = new \stdClass();
        }

        return parent::toArray();
    }

    /**
     * Adds a query to the current object.
     *
     * @param string              $type Query type
     * @param AbstractQuery|array $args Query
     *
     * @throws InvalidException If not valid query
     *
     * @return $this
     */
    protected function _addQuery(string $type, $args): self
    {
        if (!\is_array($args) && !($args instanceof AbstractQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->addParam($type, $args);
    }
}
