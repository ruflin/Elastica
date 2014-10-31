<?php

namespace Elastica\Query;
use Elastica\Exception\InvalidException;

/**
 * DisMax query
 *
 * @category Xodoa
 * @package Elastica
 * @author Hung Tran <oohnoitz@gmail.com>
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-dis-max-query.html
 */
class DisMax extends AbstractQuery
{
    /**
     * Adds a query to the current object
     *
     * @param  \Elastica\Query\AbstractQuery|array  $args Query
     * @return \Elastica\Query\DisMax
     * @throws \Elastica\Exception\InvalidException If not valid query
     */
    public function addQuery($args)
    {
        if ($args instanceof AbstractQuery) {
            $args = $args->toArray();
        }

        if (!is_array($args)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->addParam('queries', $args);
    }

    /**
     * Set boost
     *
     * @param  float                  $boost
     * @return \Elastica\Query\DisMax
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Sets tie breaker to multiplier value to balance the scores between lower and higher scoring fields.
     *
     * If not set, defaults to 0.0
     *
     * @param  float                  $tieBreaker
     * @return \Elastica\Query\DisMax
     */
    public function setTieBreaker($tieBreaker = 0.0)
    {
        return $this->setParam('tie_breaker', $tieBreaker);
    }
}
