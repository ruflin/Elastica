<?php
/**
 * Elastica index settings object
 *
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-update-settings.html
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Index_Settings
{
	protected $_response = null;

	protected $_data = array();

	protected $_name = '';

	public function __construct(Elastica_Index $index) {
		$this->_index = $index;
	}

	/**
	 * Returns the current settings of the index
	 *
	 * If param is set, only specified setting is return
	 *
	 * @param string $setting OPTIONAL Setting name to return
	 * @return array|string|null Settings data
	 */
	public function get($setting = '') {

		$data = $this->request()->getData();
		$settings = $data[$this->_index->getName()]['settings'];

		if (!empty($setting)) {
			if (isset($settings[$setting])) {
				return $settings[$setting];
			} else {
				return null;
			}
		}
		return $settings;
	}

	/**
	 * Sets the number of replicas
	 *
	 * @param int $replicas Number of replicas
	 * @return Elastica_Response Response object
	 */
	public function setNumberOfReplicas($replicas) {
		$replicas = (int) $replicas;

		$data = array('number_of_replicas' => $replicas);

		return $this->request($data, Elastica_Request::PUT);
	}

	/**
	 * Sets the number of shards
	 *
	 * @param int $shards Number of shards
	 * @return Elastica_Response Response object
	 */
	public function setNumberOfShards($shards) {
		$shards = (int) $shards;

		$data = array('number_of_shards' => $shards);

		return $this->request($data, Elastica_Request::PUT);
	}


	/**
	 * Sets the index refresh interval
	 *
	 * Value can be for example 3s for 3 seconds or
	 * 5m for 5 minutes. -1 refreshing is disabled.
	 *
	 * @param int $interval Number of seconds
	 * @return Elastica_Response Response object
	 */
	public function setRefreshInterval($interval) {
		$data = array('refresh_interval' => $interval);
		return $this->request($data, Elastica_Request::PUT);
	}

	/**
	 * Returns the index object
	 *
	 * @return Elastica_Index Index object
	 */
	public function getIndex() {
		return $this->_index;
	}

	/**
	 * Updates the given settings for the index
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
	 * @return Elastica_Response Response object
	 */
	public function request(array $data = array(), $method = Elastica_Request::GET) {
		$path = '_settings';

		$data = array('index' => $data);
		return $this->getIndex()->request($path, $method, $data);
	}
}
