<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * DisMax query.
 *
 * @author Hung Tran <oohnoitz@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-dis-max-query.html
 */
class DisMax extends AbstractQuery
{
    /**
     * Adds a query to the current object.
     *
     * @param AbstractQuery|array $args Query
     *
     * @throws InvalidException If not valid query
     *
     * @return $this
     */
    public function addQuery($args): self
    {
        if (!\is_array($args) && !($args instanceof AbstractQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->addParam('queries', $args);
    }

    /**
     * Set boost.
     *
     * @return $this
     */
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Sets tie breaker to multiplier value to balance the scores between lower and higher scoring fields.
     *
     * If not set, defaults to 0.0
     *
     * @return $this
     */
    public function setTieBreaker(float $tieBreaker = 0.0): self
    {
        return $this->setParam('tie_breaker', $tieBreaker);
    }
}
