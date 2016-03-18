<?php

namespace Elastica\Cluster\Health;

/**
 * Wraps status information for a shard.
 *
 * @author Ray Ward <ray.ward@bigcommerce.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
 */
class Shard
{
    /**
     * @var int The shard index/number.
     */
    protected $_shardNumber;

    /**
     * @var array The shard health data.
     */
    protected $_data;

    /**
     * @param int   $shardNumber The shard index/number.
     * @param array $data        The shard health data.
     */
    public function __construct($shardNumber, $data)
    {
        $this->_shardNumber = $shardNumber;
        $this->_data = $data;
    }

    /**
     * Gets the index/number of this shard.
     *
     * @return int
     */
    public function getShardNumber()
    {
        return $this->_shardNumber;
    }

    /**
     * Gets the status of this shard.
     *
     * @return string green, yellow or red.
     */
    public function getStatus()
    {
        return $this->_data['status'];
    }

    /**
     * Is the primary active?
     *
     * @return bool
     */
    public function isPrimaryActive()
    {
        return $this->_data['primary_active'];
    }

    /**
     * Is this shard active?
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->_data['active_shards'] == 1;
    }

    /**
     * Is this shard relocating?
     *
     * @return bool
     */
    public function isRelocating()
    {
        return $this->_data['relocating_shards'] == 1;
    }

    /**
     * Is this shard initialized?
     *
     * @return bool
     */
    public function isInitialized()
    {
        return $this->_data['initializing_shards'] == 1;
    }

    /**
     * Is this shard unassigned?
     *
     * @return bool
     */
    public function isUnassigned()
    {
        return $this->_data['unassigned_shards'] == 1;
    }
}
