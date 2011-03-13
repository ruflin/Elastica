<?php
/**
 * Elastica result set
 *
 * List of all hits that are returned for a search on elasticsearch
 * Result set implents iterator
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_ResultSet implements Iterator
{
	protected $_results = array();
	protected $_position = 0;
	protected $_response = null;

	public function __construct(Elastica_Response $response) {
		$this->rewind();
		$this->_init($response);
	}

	protected function _init(Elastica_Response $response) {
		$this->_response = $response;
		$result = $response->getData();
		$this->_totalHits = $result['hits']['total'];

		if (isset($result['hits']['hits'])) {
			foreach ($result['hits']['hits'] as $hit) {
				$this->_results[] = new Elastica_Result($hit);
			}
		}
	}

	public function getResults() {
		return $this->_results;
	}

	public function getTotalHits() {
		return intval($this->_totalHits);
	}

	/**
	 * Returns response object
	 *
	 * @return Elastica_Response Response object
	 */
	public function getResponse() {
		return $this->_response;
	}

	/**
	 * Returns size of current set
	 *
	 * @return int Size of set
	 */
	public function count() {
		return sizeof($this->_results);
	}


	/**
	 * Returns the current object of the set
	 *
	 * @return mixed|bool Set object or false if not valid (no more entries)
	 */
	public function current() {
		if ($this->valid()) {
			return $this->_results[$this->key()];
		} else {
			return false;
		}
	}

	/**
	 * Sets pointer (current) to the next item of the set
	 */
	public function next() {
		$this->_position++;
		return $this->current();
	}

	/**
	 * Returns the position of the current entry
	 *
	 * @return int Current position
	 */
	public function key() {
		return $this->_position;
	}

	/**
	 * Check if an object exists at the current position
	 *
	 * @return bool True if object exists
	 */
	public function valid() {
		return isset($this->_results[$this->key()]);
	}

	/**
	 * Resets position to 0, restarts iterator
	 */
	public function rewind() {
		$this->_position = 0;
	}
}
