<?php

namespace Elastica\Aggregation;

/**
 * Class Percentiles.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-percentile-aggregation.html
 */
class Percentiles extends AbstractSimpleAggregation
{
    use Traits\KeyedTrait;
    use Traits\MissingTrait;

    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct(string $name, ?string $field = null)
    {
        parent::__construct($name);

        if (null !== $field) {
            $this->setField($field);
        }
    }

    /**
     * Set compression parameter.
     *
     * @return $this
     */
    public function setCompression(float $value): self
    {
        $compression = ['compression' => $value];

        return $this->setParam('tdigest', $compression);
    }

    /**
     * Set hdr parameter.
     *
     * @return $this
     */
    public function setHdr(string $key, float $value): self
    {
        $compression = [$key => $value];

        return $this->setParam('hdr', $compression);
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
     * @return $this
     */
    public function addPercent(float $percent): self
    {
        return $this->addParam('percents', $percent);
    }
}
