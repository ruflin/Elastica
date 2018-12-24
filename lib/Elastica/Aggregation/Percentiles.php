<?php

namespace Elastica\Aggregation;

/**
 * Class Percentiles.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-percentile-aggregation.html
 */
class Percentiles extends AbstractSimpleAggregation
{
    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct($name, $field = null)
    {
        parent::__construct($name);

        if (!is_null($field)) {
            $this->setField($field);
        }
    }

    /**
     * Set compression parameter.
     *
     * @param float $value
     *
     * @return Percentiles $this
     */
    public function setCompression(float $value): Percentiles
    {
        $compression = ['compression' => $value];

        return $this->setParam('tdigest', $compression);
    }

    /**
     * Set hdr parameter.
     *
     * @param string $key
     * @param float  $value
     *
     * @return Percentiles $this
     */
    public function setHdr(string $key, float $value): Percentiles
    {
        $compression = [$key => $value];

        return $this->setParam('hdr', $compression);
    }

    /**
     * the keyed flag is set to true which associates a unique string
     * key with each bucket and returns the ranges as a hash
     * rather than an array.
     *
     * @param bool $keyed
     *
     * @return Percentiles $this
     */
    public function setKeyed(bool $keyed = true): Percentiles
    {
        return $this->setParam('keyed', $keyed);
    }

    /**
     * Set which percents must be returned.
     *
     * @param float[] $percents
     *
     * @return Percentiles $this
     */
    public function setPercents(array $percents): Percentiles
    {
        return $this->setParam('percents', $percents);
    }

    /**
     * Add yet another percent to result.
     *
     * @param float $percent
     *
     * @return Percentiles $this
     */
    public function addPercent(float $percent): Percentiles
    {
        return $this->addParam('percents', $percent);
    }

    /**
     * Defines how documents that are missing a value should
     * be treated.
     *
     * @param float $missing
     *
     * @return Percentiles
     */
    public function setMissing(float $missing): Percentiles
    {
        return $this->setParam('missing', $missing);
    }
}
