<?php

/**
 * Wraps status information for a shard.
 *
 * @package Elastica
 * @author Ray Ward <ray.ward@bigcommerce.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
 */
class Elastica_Cluster_Health_Shard
{
    /**
     * The shard index/number.
     *
     * @var int
     */
    protected $_shardNumber;

    /**
     * The shard health data.
     *
     * @var array
     */
    protected $_data;

    /**
     * @param int $shardNumber The shard index/number.
     * @param array $data The shard health data.
     */
    public function __construct($shardNumber, $data)
    {
        $this->_shardNumber = $shardNumber;
        $this->_data = $data;
    }
}

