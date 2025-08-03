<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Node\Info;
use Elastica\Node\Stats;

/**
 * Elastica cluster node object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Node
{
    /**
     * Client.
     */
    protected Client $_client;

    /**
     * @var string Unique node id
     */
    protected $_id;

    /**
     * Node name.
     *
     * @var string Node name
     */
    protected $_name;

    /**
     * Node stats.
     *
     * @var Stats|null Node Stats
     */
    protected $_stats;

    /**
     * Node info.
     *
     * @var Info|null Node info
     */
    protected $_info;

    public function __construct(string $id, Client $client)
    {
        $this->_client = $client;
        $this->setId($id);
    }

    /**
     * Returns the unique node id, this can also be name if the id does not exist.
     */
    public function getId(): string
    {
        return $this->_id;
    }

    public function setId(string $id): self
    {
        $this->_id = $id;
        $this->refresh();

        return $this;
    }

    /**
     * Get the name of the node.
     */
    public function getName(): string
    {
        if (null === $this->_name) {
            $this->_name = $this->getInfo()->getName();
        }

        return $this->_name;
    }

    /**
     * Returns the current client object.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Return stats object of the current node.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-stats.html
     */
    public function getStats(): Stats
    {
        if (!$this->_stats) {
            $this->_stats = new Stats($this);
        }

        return $this->_stats;
    }

    /**
     * Return info object of the current node.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-info.html
     */
    public function getInfo(): Info
    {
        if (!$this->_info) {
            $this->_info = new Info($this);
        }

        return $this->_info;
    }

    /**
     * Refreshes all node information.
     *
     * This should be called after updating a node to refresh all information
     */
    public function refresh(): void
    {
        $this->_stats = null;
        $this->_info = null;
    }
}
