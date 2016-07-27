<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class Range.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-range-aggregation.html
 */
class Range extends AbstractSimpleAggregation
{
    /**
     * Add a range to this aggregation.
     *
     * @param int|float $fromValue low end of this range, exclusive (greater than or equal to)
     * @param int|float $toValue   high end of this range, exclusive (less than)
     * @param string    $key       customized key value
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addRange($fromValue = null, $toValue = null, $key = null)
    {
        if (is_null($fromValue) && is_null($toValue)) {
            throw new InvalidException('Either fromValue or toValue must be set. Both cannot be null.');
        }

        $range = [];

        if (!is_null($fromValue)) {
            $range['from'] = $fromValue;
        }

        if (!is_null($toValue)) {
            $range['to'] = $toValue;
        }

        if (!is_null($key)) {
            $range['key'] = $key;
        }

        return $this->addParam('ranges', $range);
    }

    /**
     * If set to true, a unique string key will be associated with each bucket, and ranges will be returned as an associative array.
     *
     * @param bool $keyed
     *
     * @return $this
     */
    public function setKeyedResponse($keyed = true)
    {
        return $this->setParam('keyed', (bool) $keyed);
    }
}
