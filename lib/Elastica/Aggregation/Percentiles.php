<?php
namespace Elastica\Aggregation;

/**
 * Class Percentiles.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-percentile-aggregation.html
 */
class Percentiles extends AbstractSimpleAggregation
{
    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct(string $name, string $field = null)
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
     * @return $this
     */
    public function setCompression(float $value): self
    {
        $compression = array('compression' => $value);
        return $this->setParam('tdigest', $compression);
    }

    /**
     * Set hdr parameter.
     *
     * @param string $key
     * @param float $value
     *
     * @return $this
     */
    public function setHdr(string $key, float $value): self
    {
        $compression = array($key => $value);
        return $this->setParam('hdr', $compression);
    }

    /**
     * the keyed flag is set to true which associates a unique string
     * key with each bucket and returns the ranges as a hash
     * rather than an array
     *
     * @param bool $keyed
     *
     * @return $this
     */
    public function setKeyed(bool $keyed = true): self
    {
        return $this->setParam('keyed', $keyed);
    }

    /**
     * Set which percents must be returned.
     *
     * @param float[] $percents
     *
     * @return $this
     */
    public function setPercents(array $percents): self
    {
        return $this->setParam('percents', $percents);
    }

    /**
     * Add yet another percent to result.
     *
     * @param float $percent
     *
     * @return $this
     */
    public function addPercent(float $percent): self
    {
        return $this->addParam('percents', $percent);
    }

    /**
     * Defines how documents that are missing a value should
     * be treated
     *
     * @param float $missing
     *
     * @return $this
     */
    public function setMissing(float $missing): self
    {
        return $this->setParam('missing', $missing);
    }
}
