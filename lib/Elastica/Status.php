<?php
namespace Elastica;

use Elastica\Exception\ResponseException;

/**
 * Elastica general status.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/indices-status.html
 */
class Status
{
    /**
     * Contains all status infos.
     *
     * @var \Elastica\Response Response object
     */
    protected $_response = null;

    /**
     * Data.
     *
     * @var array Data
     */
    protected $_data = array();

    /**
     * Client object.
     *
     * @var \Elastica\Client Client object
     */
    protected $_client = null;

    /**
     * Constructs Status object.
     *
     * @param \Elastica\Client $client Client object
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Returns status data.
     *
     * @return array Status data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Returns a list of the existing index names.
     *
     * @return array Index names list
     */
    public function getIndexNames()
    {
        return array_keys($this->_data['indices']);
    }

    /**
     * Checks if the given index exists.
     *
     * @param string $name Index name to check
     *
     * @return bool True if index exists
     */
    public function indexExists($name)
    {
        return in_array($name, $this->getIndexNames());
    }

    /**
     * Checks if the given alias exists.
     *
     * @param string $name Alias name
     *
     * @return bool True if alias exists
     */
    public function aliasExists($name)
    {
        return count($this->getIndicesWithAlias($name)) > 0;
    }

    /**
     * Returns an array with all indices that the given alias name points to.
     *
     * @param string $alias Alias name
     *
     * @return array|\Elastica\Index[] List of Elastica\Index
     */
    public function getIndicesWithAlias($alias)
    {
        $response = null;
        try {
            $response = $this->_client->request('/_alias/'.$alias);
        } catch (ResponseException $e) {
            $transferInfo = $e->getResponse()->getTransferInfo();
            // 404 means the index alias doesn't exist which means no indexes have it.
            if ($transferInfo['http_code'] === 404) {
                return array();
            }
            // If we don't have a 404 then this is still unexpected so rethrow the exception.
            throw $e;
        }
        $indices = array();
        foreach ($response->getData() as $name => $unused) {
            $indices[] = new Index($this->_client, $name);
        }

        return $indices;
    }

    /**
     * Returns response object.
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Return shards info.
     *
     * @return array Shards info
     */
    public function getShards()
    {
        return $this->_data['shards'];
    }

    /**
     * Refresh status object.
     */
    public function refresh()
    {
        $path = '_stats';
        $this->_response = $this->_client->request($path, Request::GET);
        $this->_data = $this->getResponse()->getData();
    }
}
