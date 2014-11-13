<?php

namespace Elastica\Aggregation;

/**
 * Class GeohashGrid
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-geohashgrid-aggregation.html
 */
class GeohashGrid extends AbstractAggregation
{
    /**
     * @param string $name the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct($name, $field)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * Set the field for this aggregation
     * @param string $field the name of the document field on which to perform this aggregation
     * @return GeohashGrid
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the precision for this aggregation
     * @param int $precision an integer between 1 and 12, inclusive. Defaults to 5.
     * @return GeohashGrid
     */
    public function setPrecision($precision)
    {
        return $this->setParam("precision", $precision);
    }

    /**
     * Set the maximum number of buckets to return
     * @param int $size defaults to 10,000
     * @return GeohashGrid
     */
    public function setSize($size)
    {
        return $this->setParam("size", $size);
    }

    /**
     * Set the number of results returned from each shard
     * @param int $shardSize
     * @return GeohashGrid
     */
    public function setShardSize($shardSize)
    {
        return $this->setParam("shard_size", $shardSize);
    }
} 