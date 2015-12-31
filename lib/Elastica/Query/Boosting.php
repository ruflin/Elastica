<?php
namespace Elastica\Query;

/**
 * Class Boosting.
 *
 * @author Balazs Nadasdi <yitsushi@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html
 */
class Boosting extends AbstractQuery
{
    const NEGATIVE_BOOST = 0.2;

    /**
     * Set the positive query for this Boosting Query.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function setPositiveQuery(AbstractQuery $query)
    {
        return $this->setParam('positive', $query);
    }

    /**
     * Set the negative query for this Boosting Query.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function setNegativeQuery(AbstractQuery $query)
    {
        return $this->setParam('negative', $query);
    }

    /**
     * Set the negative_boost parameter for this Boosting Query.
     *
     * @param float $negativeBoost
     *
     * @return $this
     */
    public function setNegativeBoost($negativeBoost)
    {
        return $this->setParam('negative_boost', (float) $negativeBoost);
    }
}
