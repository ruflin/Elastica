<?php

namespace Elastica\Query;

/**
 * Class Boosting.
 *
 * @author Balazs Nadasdi <yitsushi@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html
 */
class Boosting extends AbstractQuery
{
    public const NEGATIVE_BOOST = 0.2;

    /**
     * Set the positive query for this Boosting Query.
     *
     * @return $this
     */
    public function setPositiveQuery(AbstractQuery $query): self
    {
        return $this->setParam('positive', $query);
    }

    /**
     * Set the negative query for this Boosting Query.
     *
     * @return $this
     */
    public function setNegativeQuery(AbstractQuery $query): self
    {
        return $this->setParam('negative', $query);
    }

    /**
     * Set the negative_boost parameter for this Boosting Query.
     *
     * @return $this
     */
    public function setNegativeBoost(float $negativeBoost): self
    {
        return $this->setParam('negative_boost', $negativeBoost);
    }
}
