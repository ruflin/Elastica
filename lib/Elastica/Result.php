<?php
/**
 * Elastica result item
 *
 * Stores all information from a result
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Result
{
	protected $_hit;

	/**
	 * Constructs a single results object
	 *
	 * @param array $hit Hit data
	 */
	public function __construct(array $hit) {
		$this->_hit = $hit;
	}

	/**
	 * @return int Hit id
	 */
	public function getId() {
		return $this->_hit['_id'];
	}

	/**
	 * Returns results type
	 *
	 * @return string Result type
	 */
	public function getType() {
		return $this->_hit['_type'];
	}

	/**
	 * Returns the index name of the result
	 *
	 * @return string Index name
	 */
	public function getIndex() {
		return $this->_hit['_index'];
	}

	/**
	 * Returns the score of the result
	 *
	 * @return float Results score
	 */
	public function getScore() {
		return $this->_hit['_score'];
	}

	/**
	 * Returns the raw hit array
	 *
	 * @return array Hit array
	 */
	public function getHit() {
		return $this->_hit;
	}

	/**
	 * Returns the version information from the hit
	 *
	 * @return string|int Document version
	 */
	public function getVersion() {
		return $this->_hit['_version'];
	}

	/**
	 * Returns result data
	 *
	 * Alias for getSource
	 *
	 * @return array Result data array
	 */
	public function getData() {
		return $this->getSource();
	}

	/**
	 * Returns the result source
	 *
	 * @return array Source data array
	 */
	public function getSource() {
		if (isset($this->_hit['_source'])) {
			return $this->_hit['_source'];
		} else {
			return array();
		}
	}

	/**
	 * Returns result data
	 *
	 * @return array Result data array
	 */
	public function getHighlights() {
		if (isset($this->_hit['highlight'])) {
			return $this->_hit['highlight'];
		} else {
			return array();
		}
	}

	/**
	 * Returns explanation on how its score was computed.
	 *
	 * @return array explanations
	 */
	public function getExplanation() {
		if (isset($this->_hit['_explanation'])) {
			return $this->_hit['_explanation'];
		} else {
			return array();
		}
	}

	/**
	 * Magic function to directly access keys inside the result
	 *
	 * Returns null if key does not exist
	 *
	 * @param string $key Key name
	 * @return mixed Key value
	 */
	public function __get($key) {
		$source = $this->getData();
		return array_key_exists($key, $source) ? $source[$key] : null;
	}
}
