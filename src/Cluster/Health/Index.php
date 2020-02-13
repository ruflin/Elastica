<?php

namespace Elastica\Cluster\Health;

/**
 * Wraps status information for an index.
 *
 * @author Ray Ward <ray.ward@bigcommerce.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
 */
class Index
{
    /**
     * @var string the name of the index
     */
    protected $_name;

    /**
     * @var array the index health data
     */
    protected $_data;

    /**
     * @param string $name the name of the index
     * @param array  $data the index health data
     */
    public function __construct(string $name, array $data)
    {
        $this->_name = $name;
        $this->_data = $data;
    }

    /**
     * Gets the name of the index.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Gets the status of the index.
     *
     * @return string green, yellow or red
     */
    public function getStatus(): string
    {
        return $this->_data['status'];
    }

    /**
     * Gets the number of nodes in the index.
     */
    public function getNumberOfShards(): int
    {
        return $this->_data['number_of_shards'];
    }

    /**
     * Gets the number of data nodes in the index.
     */
    public function getNumberOfReplicas(): int
    {
        return $this->_data['number_of_replicas'];
    }

    /**
     * Gets the number of active primary shards.
     */
    public function getActivePrimaryShards(): int
    {
        return $this->_data['active_primary_shards'];
    }

    /**
     * Gets the number of active shards.
     */
    public function getActiveShards(): int
    {
        return $this->_data['active_shards'];
    }

    /**
     * Gets the number of relocating shards.
     */
    public function getRelocatingShards(): int
    {
        return $this->_data['relocating_shards'];
    }

    /**
     * Gets the number of initializing shards.
     */
    public function getInitializingShards(): int
    {
        return $this->_data['initializing_shards'];
    }

    /**
     * Gets the number of unassigned shards.
     */
    public function getUnassignedShards(): int
    {
        return $this->_data['unassigned_shards'];
    }

    /**
     * Gets the health of the shards in this index.
     *
     * @return Shard[]
     */
    public function getShards(): array
    {
        $shards = [];
        foreach ($this->_data['shards'] as $shardNumber => $shard) {
            $shards[] = new Shard($shardNumber, $shard);
        }

        return $shards;
    }
}
