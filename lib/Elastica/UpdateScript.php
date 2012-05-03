<?php
/**
 * Elastica update script
 *
 * elasticsearch supports update scripts to improve indexing concurrency by re-indexing documents on server-side
 * version >= 0.19 is required
 *
 * @link http://www.elasticsearch.org/guide/reference/api/update.html
 * @category Xodoa
 * @package Elastica
 * @author Alex Vasilenko <aa.vasilenko@gmail.com>
 */
class Elastica_UpdateScript {

	/**
	 * @var string
	 */
	private $_id;

	/**
	 * @var string
	 */
	private $_script;

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * Query parameters
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/index_.html for explanation
	 */

	/**
	 * @var string
	 */
	private $_percolate;

	/**
	 * @var string
	 */
	private $_parent;

	/**
	 * @var string
	 */
	private $_routing;

	/**
	 *
	 * @var string
	 */
	private $_timeout;

	/**
	 * @var string
	 */
	private $_replication;

	/**
	 * @var string
	 */
	private $_consistency;

	/**
	 * @var bool
	 */
	private $_refresh;

	/**
	 * @var int
	 */
	private $_retryOnConflict;

	/**
	 * @param string $id
	 * @param string $script
	 * @param array $data
	 */
	function __construct($id, $script, array $params = array()) {
		$this->_id = $id;
		$this->_script = $script;
		$this->_data = $params;
	}

	/**
	 * @return array
	 */
	public function prepareData() {
		$data = array(
			'script' => $this->_script,
		);
		if (!empty($this->_data)) {
			$data['params'] = $this->_data;
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public function prepareQuery() {
		$query = array();
		if (!is_null($this->_routing)) {
			$query['routing'] = $this->_routing;
		}

		if (!is_null($this->_consistency)) {
			$query['consistency'] = $this->_consistency;
		}

		if (!is_null($this->_parent)) {
			$query['parent'] = $this->_parent;
		}

		if (!is_null($this->_refresh)) {
			$query['refresh'] = $this->_refresh;
		}

		if (!is_null($this->_percolate)) {
			$query['percolate'] = $this->_percolate;
		}

		if (!is_null($this->_replication)) {
			$query['replication'] = $this->_replication;
		}

		if (!is_null($this->_retryOnConflict)) {
			$query['retry_on_conflict'] = $this->_retryOnConflict;
		}

		if (!is_null($this->_timeout)) {
			$query['timeout'] = $this->_timeout;
		}

		if (!is_null($this->_routing)) {
			$query['routing'] = $this->_routing;
		}

		return $query;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getScript() {
		return $this->_script;
	}

	/**
	 * @param string $consistency
	 */
	public function setConsistency($consistency) {
		$this->_consistency = $consistency;
	}

	/**
	 * @return string
	 */
	public function getConsistency() {
		return $this->_consistency;
	}

	/**
	 * @param string $parent
	 */
	public function setParent($parent) {
		$this->_parent = $parent;
	}

	/**
	 * @return string
	 */
	public function getParent() {
		return $this->_parent;
	}

	/**
	 * @param string $percolate
	 */
	public function setPercolate($percolate) {
		$this->_percolate = $percolate;
	}

	/**
	 * @return string
	 */
	public function getPercolate() {
		return $this->_percolate;
	}

	/**
	 * @param boolean $refresh
	 */
	public function setRefresh($refresh) {
		$this->_refresh = $refresh;
	}

	/**
	 * @return boolean
	 */
	public function getRefresh() {
		return $this->_refresh;
	}

	/**
	 * @param string $replication
	 */
	public function setReplication($replication) {
		$this->_replication = $replication;
	}

	/**
	 * @return string
	 */
	public function getReplication() {
		return $this->_replication;
	}

	/**
	 * @param string $routing
	 */
	public function setRouting($routing) {
		$this->_routing = $routing;
	}

	/**
	 * @return string
	 */
	public function getRouting() {
		return $this->_routing;
	}

	/**
	 * @param string $timeout
	 */
	public function setTimeout($timeout) {
		$this->_timeout = $timeout;
	}

	/**
	 * @return string
	 */
	public function getTimeout() {
		return $this->_timeout;
	}

	/**
	 * @param int $retryOnConflict
	 */
	public function setRetryOnConflict($retryOnConflict) {
		$this->_retryOnConflict = $retryOnConflict;
	}

	/**
	 * @return int
	 */
	public function getRetryOnConflict() {
		return $this->_retryOnConflict;
	}
}
