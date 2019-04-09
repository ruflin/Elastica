<?php

namespace Elastica\Aggregation;

use Elastica\Query\AbstractQuery;

/**
 * Class GlobalAggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-global-aggregation.html
 */
class GlobalAggregation extends AbstractAggregation
{
    /**
     * @param string        $name
     * @param AbstractQuery $filter
     */
    public function __construct($name, AbstractQuery $filter = null)
    {
        parent::__construct($name);

        if (null !== $filter) {
            $this->setFilter($filter);
        }
    }

    /**
     * Set the filter for this aggregation.
     *
     *
     * @param AbstractQuery $filter
     *
     * @return $this
     */
    public function setFilter(AbstractQuery $filter)
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = ['global' => new \stdClass()];
        if ($this->hasParam('filter')) {
            $array['aggs'] =
                [
                    'all' => [
                        'filter' => $this->getParam('filter')->toArray(),
                    ],
                ];
        }

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
