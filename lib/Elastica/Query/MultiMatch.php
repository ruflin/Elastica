<?php

namespace Elastica\Query;

/**
 * Multi Match
 *
 * @category Xodoa
 * @package Elastica
 * @author Rodolfo Adhenawer Campagnoli Moraes <adhenawer@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/multi-match-query.html
 */
class MultiMatch extends AbstractQuery
{

    /**
     * Sets the query
     *
     * @param  string                         $query Query
     * @return \Elastica\Query\MultiMatch Current object
     */
    public function setQuery($query = '')
    {
        return $this->setParam('query', $query);
    }

    /**
     * Sets Fields to be used in the query.
     *
     * @param  array                          $fields Fields
     * @return \Elastica\Query\MultiMatch Current object
     */
    public function setFields($fields = array())
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets use dis max indicating to either create a dis_max query or a bool query.
     *
     * If not set, defaults to true.
     *
     * @param  boolean                        $useDisMax
     * @return \Elastica\Query\MultiMatch Current object
     */
    public function setUseDisMax($useDisMax = true)
    {
        return $this->setParam('use_dis_max', $useDisMax);
    }

    /**
     * Sets tie breaker to multiplier value to balance the scores between lower and higher scoring fields.
     *
     * If not set, defaults to 0.0.
     *
     * @param  float                          $tieBreaker
     * @return \Elastica\Query\MultiMatch Current object
     */
    public function setTieBreaker($tieBreaker = 0.0)
    {
        return $this->setParam('tie_breaker', $tieBreaker);
    }
}
