<?php

declare(strict_types=1);

namespace Elastica\Node;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
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
    protected BaseNode $_node;

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
     * @return array|null Node stats for the given field or null if not found
     */
    public function get(...$args)
    {
        $data = $this->getData();

        foreach ($args as $arg) {
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
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    /**
     * Reloads all nodes information. Has to be called if informations changed.
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function refresh(): Response
    {
        $client = $this->getNode()->getClient();
        $this->_response = $client->toElasticaResponse(
            $client->nodes()->stats(['node_id' => $this->getNode()->getName()])
        );
        $data = $this->getResponse()->getData();
        $this->_data = \reset($data['nodes']);

        return $this->_response;
    }
}
