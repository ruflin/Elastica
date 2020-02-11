<?php

namespace Elastica\Node;

use Elastica\Node as BaseNode;
use Elastica\Response;

/**
 * Elastica cluster node object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-status.html
 */
class Stats
{
    /**
     * Response.
     *
     * @var Response Response object
     */
    protected $_response;

    /**
     * Stats data.
     *
     * @var array stats data
     */
    protected $_data = [];

    /**
     * Node.
     *
     * @var BaseNode Node object
     */
    protected $_node;

    /**
     * Create new stats for node.
     *
     * @param BaseNode $node Elastica node object
     */
    public function __construct(BaseNode $node)
    {
        $this->_node = $node;
        $this->refresh();
    }

    /**
     * Returns all node stats as array based on the arguments.
     *
     * Several arguments can be use
     * get('index', 'test', 'example')
     *
     * @return array Node stats for the given field or null if not found
     */
    public function get()
    {
        $data = $this->getData();

        foreach (\func_get_args() as $arg) {
            if (isset($data[$arg])) {
                $data = $data[$arg];
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Returns all stats data.
     *
     * @return array Data array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Returns node object.
     *
     * @return BaseNode Node object
     */
    public function getNode(): BaseNode
    {
        return $this->_node;
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    /**
     * Reloads all nodes information. Has to be called if informations changed.
     *
     * @return Response Response object
     */
    public function refresh(): Response
    {
        $endpoint = new \Elasticsearch\Endpoints\Cluster\Nodes\Stats();
        $endpoint->setNodeID($this->getNode()->getName());

        $this->_response = $this->getNode()->getClient()->requestEndpoint($endpoint);
        $data = $this->getResponse()->getData();
        $this->_data = \reset($data['nodes']);

        return $this->_response;
    }
}
