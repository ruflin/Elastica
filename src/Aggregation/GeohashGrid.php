<?php

namespace Elastica\Aggregation;

/**
 * Class GeohashGrid.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geohashgrid-aggregation.html
 */
class GeohashGrid extends AbstractAggregation
{
    use Traits\ShardSizeTrait;

    public const DEFAULT_PRECISION_VALUE = 5;
    public const DEFAULT_SIZE_VALUE = 10000;

    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct(string $name, string $field)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * Set the field for this aggregation.
     *
     * @param string $field the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the precision for this aggregation.
     *
     * @param int|string $precision an integer between 1 and 12, inclusive. Defaults to 5 or distance like 1km, 10m
     *
     * @return $this
     */
    public function setPrecision($precision): self
    {
        if (!\is_int($precision) && !\is_string($precision)) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be of type int|string, %s given.', __METHOD__, \is_object($precision) ? \get_class($precision) : \gettype($precision)));
        }

        return $this->setParam('precision', $precision);
    }

    /**
     * Set the maximum number of buckets to return.
     *
     * @param int $size defaults to 10,000
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }
}
