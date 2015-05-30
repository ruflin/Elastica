<?php
namespace Elastica\Index;

use Elastica\Index as BaseIndex;
use Elastica\Request;

/**
 * Elastica index status object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/indices-status.html
 */
class Status
{
    /**
     * Response.
     *
     * @var \Elastica\Response Response object
     */
    protected $_response = null;

    /**
     * Stats info.
     *
     * @var array Stats info
     */
    protected $_data = array();

    /**
     * Index.
     *
     * @var \Elastica\Index Index object
     */
    protected $_index = null;

    /**
     * Construct.
     *
     * @param \Elastica\Index $index Index object
     */
    public function __construct(BaseIndex $index)
    {
        $this->_index = $index;
        $this->refresh();
    }

    /**
     * Returns all status info.
     *
     * @return array Status info
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Returns the entry in the data array based on the params.
     * Various params possible.
     *
     * @return mixed Data array entry or null if not found
     */
    public function get()
    {
        $data = $this->getData();
        $data = $data['indices'][$this->getIndex()->getName()];

        foreach (func_get_args() as $arg) {
            if (isset($data[$arg])) {
                $data = $data[$arg];
            } else {
                return;
            }
        }

        return $data;
    }

    /**
     * Returns all index aliases.
     *
     * @return array Aliases
     */
    public function getAliases()
    {
        $responseData = $this->getIndex()->request('_aliases', \Elastica\Request::GET)->getData();

        $data = $responseData[$this->getIndex()->getName()];
        if (!empty($data['aliases'])) {
            return array_keys($data['aliases']);
        }

        return array();
    }

    /**
     * Returns all index settings.
     *
     * @return array Index settings
     */
    public function getSettings()
    {
        $responseData = $this->getIndex()->request('_settings', \Elastica\Request::GET)->getData();

        return $responseData[$this->getIndex()->getName()]['settings'];
    }

    /**
     * Checks if the index has the given alias.
     *
     * @param string $name Alias name
     *
     * @return bool
     */
    public function hasAlias($name)
    {
        return in_array($name, $this->getAliases());
    }

    /**
     * Returns the index object.
     *
     * @return \Elastica\Index Index object
     */
    public function getIndex()
    {
        return $this->_index;
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
     * Reloads all status data of this object.
     */
    public function refresh()
    {
        $path = '_status';
        $this->_response = $this->getIndex()->request($path, Request::GET);
        $this->_data = $this->getResponse()->getData();
    }
}
