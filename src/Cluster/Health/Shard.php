<?php

namespace Elastica\Cluster\Health;

/**
 * Wraps status information for a shard.
 *
 * @author Ray Ward <ray.ward@bigcommerce.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
 *
 * @phpstan-import-type HealthStatus from \Elastica\Cluster\Health
 * @phpstan-type ShardData = array{
 *   status: HealthStatus,
 *   primary_active: bool,
 *   active_shards: int,
 *   relocating_shards: int,
 *   initializing_shards: int,
 *   unassigned_shards: int,
 * }
 */
class Shard
{
    /**
     * @var int the shard index/number
     */
    protected $_shardNumber;

    /**
     * @var array<string, mixed> the shard health data
     * @phpstan-var ShardData
     */
    protected $_data;

    /**
     * @param int                  $shardNumber the shard index/number
     * @param array<string, mixed> $data        the shard health data
     * @phpstan-param ShardData $data
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
