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
	 * Returns a param from the result hit array
	 *
	 * This function can be used to retriev all data for which not a specific
	 * function exists.
	 * If the param does not exists and empty array is retured
	 *
	 * @param string $name Param name
	 * @return array Result data
	 */
	public function getParam($name) {
		if (isset($this->_hit[$name])) {
			return $this->_hit[$name];
		} else {
			return array();
		}
	}

	/**
	 * Returns the hit id.
	 *
	 * The id is not stored anymore by default in the index since
	 * elasticsearch 0.16.0 {@link https://github.com/elasticsearch/elasticsearch/issues/868}
	 *
	 * @return string Hit id
	 * @throws Elastica_Exception_Invalid If id is not set
	 */
	public function getId() {
		$id = $this->getParam('_id');

		if (empty($id)) {
			throw new Elastica_Exception_Invalid('No hit id. _id is not stored by default anymore since 0.16.0');
		}

		return $id;
	}

	/**
	 * Returns results type
	 *
	 * @return string Result type
	 */
	public function getType() {
		return $this->getParam('_type');
	}

	/**
	 * Returns list of fields
	 *
	 * @return array Fields list
	 */
	public function getFields() {
		return $this->getParam('_fields');
	}

	/**
	 * Returns the index name of the result
	 *
	 * @return string Index name
	 */
	public function getIndex() {
		return $this->getParam('_index');
	}

	/**
	 * Returns the score of the result
	 *
	 * @return float Results score
	 */
	public function getScore() {
		return $this->getParam('_score');
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
		return $this->getParam('_version');
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
		return $this->getParam('_source');
	}

	/**
	 * Returns result data
	 *
	 * @return array Result data array
	 */
	public function getHighlights() {
		return $this->getParam('highlight');
	}

	/**
	 * Returns explanation on how its score was computed.
	 *
	 * @return array explanations
	 */
	public function getExplanation() {
		return $this->getParam('_explanation');
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
