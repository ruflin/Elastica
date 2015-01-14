<?php

namespace Elastica\Query;

/**
 * Class Boosting
 * @package Elastica\Query
 * @author Balazs Nadasdi <yitsushi@gmail.com>
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html
 */
class Boosting extends AbstractQuery
{
    const NEGATIVE_BOOST = 0.2;

    /**
     * Set the positive query for this Boosting Query
     * @param  AbstractQuery            $query
     * @return \Elastica\Query\Boosting
     */
    public function setPositiveQuery(AbstractQuery $query)
    {
        return $this->setParam('positive', $query->toArray());
    }

    /**
     * Set the negative query for this Boosting Query
     * @param  AbstractQuery            $query
     * @return \Elastica\Query\Boosting
     */
    public function setNegativeQuery(AbstractQuery $query)
    {
        return $this->setParam('negative', $query->toArray());
    }

    /**
     * Set the negative_boost parameter for this Boosting Query
     * @param  Float                    $negativeBoost
     * @return \Elastica\Query\Boosting
     */
    public function setNegativeBoost($negativeBoost)
    {
        return $this->setParam('negative_boost', (float) $negativeBoost);
    }
}
