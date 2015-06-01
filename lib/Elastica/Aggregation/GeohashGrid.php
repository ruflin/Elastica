<?php
namespace Elastica\Aggregation;

/**
 * Class GeohashGrid.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geohashgrid-aggregation.html
 */
class GeohashGrid extends AbstractAggregation
{
    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct($name, $field)
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
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the precision for this aggregation.
     *
     * @param int $precision an integer between 1 and 12, inclusive. Defaults to 5.
     *
     * @return $this
     */
    public function setPrecision($precision)
    {
        return $this->setParam('precision', $precision);
    }

    /**
     * Set the maximum number of buckets to return.
     *
     * @param int $size defaults to 10,000
     *
     * @return $this
     */
    public function setSize($size)
    {
        return $this->setParam('size', $size);
    }

    /**
     * Set the number of results returned from each shard.
     *
     * @param int $shardSize
     *
     * @return $this
     */
    public function setShardSize($shardSize)
    {
        return $this->setParam('shard_size', $shardSize);
    }
}
