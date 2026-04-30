<?php

declare(strict_types=1);

namespace Elastica\Cluster\Health;

/**
 * Wraps status information for a shard.
 *
 * @author Ray Ward <ray.ward@bigcommerce.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
 */
class Shard
{
    /**
     * @var int the shard index/number
     */
    protected int $_shardNumber;

    /**
     * @var array the shard health data
     */
    protected array $_data;

    /**
     * @param int   $shardNumber the shard index/number
     * @param array $data        the shard health data
     */
    public function __construct(int $shardNumber, array $data)
    {
        $this->_shardNumber = $shardNumber;
        $this->_data = $data;
    }

    /**
     * Gets the index/number of this shard.
     */
    public function getShardNumber(): int
    {
        return $this->_shardNumber;
    }

    /**
     * Gets the status of this shard.
     *
     * @return string green, yellow or red
     */
    public function getStatus(): string
    {
        return $this->_data['status'];
    }

    /**
     * Is the primary active?
     */
    public function isPrimaryActive(): bool
    {
        return $this->_data['primary_active'];
    }

    /**
     * Is this shard active?
     */
    public function isActive(): bool
    {
        return 1 === $this->_data['active_shards'];
    }

    /**
     * Is this shard relocating?
     */
    public function isRelocating(): bool
    {
        return 1 === $this->_data['relocating_shards'];
    }

    /**
     * Is this shard initialized?
     */
    public function isInitialized(): bool
    {
        return 1 === $this->_data['initializing_shards'];
    }

    /**
     * Is this shard unassigned?
     */
    public function isUnassigned(): bool
    {
        return 1 === $this->_data['unassigned_shards'];
    }
}
