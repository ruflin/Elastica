<?php

namespace Elastica\Aggregation;

/**
 * Class ParentAggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-parent-aggregation.html
 */
class ParentAggregation extends AbstractAggregation
{
    /**
     * Set the child type for this aggregation.
     *
     * @param string $type the child type that should be selected
     *
     * @return $this
     */
    public function setType($type): self
    {
        return $this->setParam('type', $type);
    }

    protected function _getBaseName()
    {
        return 'parent';
    }
}
