<?php
namespace Elastica\Index;

use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elastica\Index as BaseIndex;
use Elastica\Request;

/**
 * Elastica index settings object.
 *
 * All settings listed in the update settings API (https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html)
 * can be changed on a running indices. To make changes like the merge policy (https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html)
 * the index has to be closed first and reopened after the call
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html
 */
class Settings
{
    const DEFAULT_REFRESH_INTERVAL = '1s';

    const DEFAULT_NUMBER_OF_REPLICAS = 1;

    const DEFAULT_NUMBER_OF_SHARDS = 5;

    /**
     * Response.
     *
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Stats info.
     *
     * @var array Stats info
     */
    protected $_data = [];

    /**
     * Index.
     *
     * @var \Elastica\Index Index object
     */
    protected $_index;

    /**
     * Construct.
     *
     * @param \Elastica\Index $index Index object
     */
    public function __construct(BaseIndex $index)
    {
        $this->_index = $index;
    }

    /**
     * Returns the current settings of the index.
     *
     * If param is set, only specified setting is return.
     * 'index.' is added in front of $setting.
     *
     * @param string $setting OPTIONAL Setting name to return
     *
     * @return array|string|null Settings data
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
     */
    public function get($setting = '')
    {
        $requestData = $this->request()->getData();
        $data = reset($requestData);

        if (empty($data['settings']) || empty($data['settings']['index'])) {
            // should not append, the request should throw a ResponseException
            throw new NotFoundException('Index '.$this->getIndex()->getName().' not found');
        }
        $settings = $data['settings']['index'];

        if (!$setting) {
            // return all array
            return $settings;
        }

        if (isset($settings[$setting])) {
            return $settings[$setting];
        } else {
            if (strpos($setting, '.') !== false) {
                // translate old dot-notation settings to nested arrays
                $keys = explode('.', $setting);
                foreach ($keys as $key) {
                    if (isset($settings[$key])) {
                        $settings = $settings[$key];
                    } else {
                        return;
                    }
                }

                return $settings;
            }

            return;
        }
    }

    /**
     * Returns a setting interpreted as a bool.
     *
     * One can use a real bool, int(0), int(1) to set bool settings.
     * But Elasticsearch stores and returns all settings as strings and does
     * not normalize bool values. This method ensures a bool is returned for
     * whichever string representation is used like 'true', '1', 'on', 'yes'.
     *
     * @param string $setting Setting name to return
     *
     * @return bool
     */
    public function getBool($setting)
    {
        $data = $this->get($setting);

        return 'true' === $data || '1' === $data || 'on' === $data || 'yes' === $data;
    }

    /**
     * Sets the number of replicas.
     *
     * @param int $replicas Number of replicas
     *
     * @return \Elastica\Response Response object
     */
    public function setNumberOfReplicas($replicas)
    {
        return $this->set(['number_of_replicas' => (int) $replicas]);
    }

    /**
     * Returns the number of replicas.
     *
     * If no number of replicas is set, the default number is returned
     *
     * @return int The number of replicas
     */
    public function getNumberOfReplicas()
    {
        $replicas = $this->get('number_of_replicas');

        if (null === $replicas) {
            $replicas = self::DEFAULT_NUMBER_OF_REPLICAS;
        }

        return $replicas;
    }

    /**
     * Returns the number of shards.
     *
     * If no number of shards is set, the default number is returned
     *
     * @return int The number of shards
     */
    public function getNumberOfShards()
    {
        $shards = $this->get('number_of_shards');

        if (null === $shards) {
            $shards = self::DEFAULT_NUMBER_OF_SHARDS;
        }

        return $shards;
    }

    /**
     * Sets the index to read only.
     *
     * @param bool $readOnly (default = true)
     *
     * @return \Elastica\Response
     */
    public function setReadOnly($readOnly = true)
    {
        return $this->set(['blocks.read_only' => $readOnly]);
    }

    /**
     * @return bool
     */
    public function getReadOnly()
    {
        return $this->getBool('blocks.read_only');
    }

    /**
     * @return bool
     */
    public function getBlocksRead()
    {
        return $this->getBool('blocks.read');
    }

    /**
     * @param bool $state OPTIONAL (default = true)
     *
     * @return \Elastica\Response
     */
    public function setBlocksRead($state = true)
    {
        return $this->set(['blocks.read' => $state]);
    }

    /**
     * @return bool
     */
    public function getBlocksWrite()
    {
        return $this->getBool('blocks.write');
    }

    /**
     * @param bool $state OPTIONAL (default = true)
     *
     * @return \Elastica\Response
     */
    public function setBlocksWrite($state = true)
    {
        return $this->set(['blocks.write' => $state]);
    }

    /**
     * @return bool
     */
    public function getBlocksMetadata()
    {
        // When blocks.metadata is enabled, reading the settings is not possible anymore.
        // So when a cluster_block_exception happened it must be enabled.
        try {
            return $this->getBool('blocks.metadata');
        } catch (ResponseException $e) {
            if ($e->getResponse()->getFullError()['type'] === 'cluster_block_exception') {
                return true;
            }

            throw $e;
        }
    }

    /**
     * Set to true to disable index metadata reads and writes.
     *
     * @param bool $state OPTIONAL (default = true)
     *
     * @return \Elastica\Response
     */
    public function setBlocksMetadata($state = true)
    {
        return $this->set(['blocks.metadata' => $state]);
    }

    /**
     * Sets the index refresh interval.
     *
     * Value can be for example 3s for 3 seconds or
     * 5m for 5 minutes. -1 refreshing is disabled.
     *
     * @param int $interval Number of milliseconds
     *
     * @return \Elastica\Response Response object
     */
    public function setRefreshInterval($interval)
    {
        return $this->set(['refresh_interval' => $interval]);
    }

    /**
     * Returns the refresh interval.
     *
     * If no interval is set, the default interval is returned
     *
     * @return string Refresh interval
     */
    public function getRefreshInterval()
    {
        $interval = $this->get('refresh_interval');

        if (empty($interval)) {
            $interval = self::DEFAULT_REFRESH_INTERVAL;
        }

        return $interval;
    }

    /**
     * Sets the specific merge policies.
     *
     * To have this changes made the index has to be closed and reopened
     *
     * @param string $key   Merge policy key (for ex. expunge_deletes_allowed)
     * @param string $value
     *
     * @return \Elastica\Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html
     */
    public function setMergePolicy($key, $value)
    {
        $this->getIndex()->close();
        $response = $this->set(['merge.policy.'.$key => $value]);
        $this->getIndex()->open();

        return $response;
    }

    /**
     * Returns the specific merge policy value.
     *
     * @param string $key Merge policy key (for ex. expunge_deletes_allowed)
     *
     * @return string Refresh interval
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html
     */
    public function getMergePolicy($key)
    {
        $settings = $this->get();
        if (isset($settings['merge']['policy'][$key])) {
            return $settings['merge']['policy'][$key];
        }

        return;
    }

    /**
     * Can be used to set/update settings.
     *
     * @param array $data Arguments
     *
     * @return \Elastica\Response Response object
     */
    public function set(array $data)
    {
        return $this->request($data, Request::PUT);
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
     * Updates the given settings for the index.
     *
     * With elasticsearch 0.16 the following settings are supported
     * - index.term_index_interval
     * - index.term_index_divisor
     * - index.translog.flush_threshold_ops
     * - index.translog.flush_threshold_size
     * - index.translog.flush_threshold_period
     * - index.refresh_interval
     * - index.merge.policy
     * - index.auto_expand_replicas
     *
     * @param array  $data   OPTIONAL Data array
     * @param string $method OPTIONAL Transfer method (default = \Elastica\Request::GET)
     *
     * @return \Elastica\Response Response object
     */
    public function request(array $data = [], $method = Request::GET)
    {
        $path = '_settings';

        if (!empty($data)) {
            $data = ['index' => $data];
        }

        return $this->getIndex()->request($path, $method, $data);
    }
}
