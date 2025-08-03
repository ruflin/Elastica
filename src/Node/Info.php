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
class Info
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
     * Query parameters.
     *
     * @var array
     */
    protected $_params = [];

    /**
     * Unique node id.
     *
     * @var string
     */
    protected $_id;

    /**
     * Create new info object for node.
     *
     * @param BaseNode $node   Node object
     * @param array    $params List of params to return. Can be: settings, os, process, jvm, thread_pool, network, transport, http
     */
    public function __construct(BaseNode $node, array $params = [])
    {
        $this->_node = $node;
        $this->refresh($params);
    }

    /**
     * Returns the entry in the data array based on the params.
     * Several params possible.
     *
     * Example 1: get('os', 'mem', 'total') returns total memory of the system the
     * node is running on
     * Example 2: get('os', 'mem') returns an array with all mem infos
     *
     * @return mixed Data array entry or null if not found
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
     * Return port of the node.
     *
     * @return string Returns Node port
     */
    public function getPort(): string
    {
        // Returns string in format: inet[/192.168.1.115:9201]
        $data = $this->get('http_address');
        $data = \substr($data, 6, -1);
        $data = \explode(':', $data);

        return $data[1];
    }

    /**
     * Return IP of the node.
     *
     * @return string Returns Node ip address
     */
    public function getIp(): string
    {
        // Returns string in format: inet[/192.168.1.115:9201]
        $data = $this->get('http_address');
        $data = \substr($data, 6, -1);
        $data = \explode(':', $data);

        return $data[0];
    }

    /**
     * Return data regarding plugins installed on this node.
     *
     * @return array plugin data
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-info.html
     */
    public function getPlugins(): array
    {
        if (!\in_array('plugins', $this->_params, true)) {
            // Plugin data was not retrieved when refresh() was called last. Get it now.
            $this->_params[] = 'plugins';
            $this->refresh($this->_params);
        }

        return $this->get('plugins');
    }

    /**
     * Check if the given plugin is installed on this node.
     *
     * @param string $name plugin name
     *
     * @return bool true if the plugin is installed, false otherwise
     */
    public function hasPlugin($name): bool
    {
        foreach ($this->getPlugins() as $plugin) {
            if ($plugin['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return all info data.
     *
     * @return array Data array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Return node object.
     *
     * @return BaseNode Node object
     */
    public function getNode(): BaseNode
    {
        return $this->_node;
    }

    /**
     * @return string Unique node id
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * @return string Node name
     */
    public function getName(): string
    {
        return $this->_data['name'];
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
     * @param array $params Params to return (default none). Possible options: settings, os, process, jvm, thread_pool, network, transport, http, plugin
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function refresh(array $params = []): Response
    {
        $this->_params = $params;
        $client = $this->getNode()->getClient();

        $paramsRequest['node_id'] = $this->getNode()->getId();

        if ($params) {
            $paramsRequest['metric'] = $params;
        }

        $this->_response = $client->toElasticaResponse($client->nodes()->info($paramsRequest));
        $data = $this->getResponse()->getData();

        $this->_data = \reset($data['nodes']);
        $this->_id = \key($data['nodes']);
        $this->getNode()->setId($this->getId());

        return $this->_response;
    }
}
