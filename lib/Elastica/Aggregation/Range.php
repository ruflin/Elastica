<?php
namespace Elastica\Aggregation;


use Elastica\Exception\InvalidException;

/**
 * Class Range
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-range-aggregation.html
 */
class Range extends AbstractSimpleAggregation
{
    /**
     * Add a range to this aggregation
     * @param int|float $fromValue low end of this range, exclusive (greater than)
     * @param int|float $toValue high end of this range, exclusive (less than)
     * @return Range
     * @throws \Elastica\Exception\InvalidException
     */
    public function addRange($fromValue = null, $toValue = null)
    {
        if (is_null($fromValue) && is_null($toValue)) {
            throw new InvalidException("Either fromValue or toValue must be set. Both cannot be null.");
        }
        $range = array();
        if (!is_null($fromValue)) {
            $range['from'] = $fromValue;
        }
        if (!is_null($toValue)) {
            $range['to'] = $toValue;
        }
        return $this->addParam('ranges', $range);
    }

    /**
     * If set to true, a unique string key will be associated with each bucket, and ranges will be returned as an associative array
     * @param bool $keyed
     * @return Range
     */
    public function setKeyedResponse($keyed = true)
    {
        return $this->setParam('keyed', (bool)$keyed);
    }
} 