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
		$settings = $this->_index->getStatus()->getSettings();

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

		return $this->request($data);
	}

	/**
	 * Sets the index refresh interval in seconds
	 *
	 * @param int $seconds Number of seconds
	 * @return Elastica_Response Response object
	 */
	public function setRefreshInterval($seconds) {
		$seconds = (int) $seconds;
		$data = array('refresh_interval' => $seconds . 's');
		return $this->request($data);
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
	public function request(array $data = array()) {
		$path = '_settings';

		$data = array('index' => $data);
		$this->_response = $this->getIndex()->request($path, Elastica_Request::GET);
		return $this->getResponse();
	}
}
