<?php
/**
 * Fuzzy Like This query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Raul Martinez, Jr <juneym@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/flt-query.html
 */
class Elastica_Query_FuzzyLikeThis extends Elastica_Query_Abstract
{
	protected $_fields = array();
	protected $_likeText   = null;
	protected $_maxQueryTerms = 25;
	protected $_minSimilarity = 0.5;
	protected $_prefixLength = 0;
	protected $_boost = 1.0;

	/**
	 * Adds field to flt query
	 *
	 * @param array $fields Field names
	 * @return Elastica_Query_FuzzyLikeThis Current object
	 */
	public function addFields(array $fields) {
		$this->_fields = $fields;
		return $this;
	}

	/**
	 * Set the "like_text" value
	 *
	 * @param string $text
	 * @return Elastica_Query_FuzzyLikeThis This current object
	 */
	public function setLikeText($text) {
		$text = trim($text);
		$this->_likeText = $text;
		return $this;
	}

	/**
	 * Set the minimum similarity
	 *
	 * @param int $value
	 * @return Elastica_Query_FuzzyLikeThis This current object
	 */
	public function setMinSimilarity($value) {
		$value = (float) $value;
		$this->_minSimilarity = $value;
		return $this;
	}

	/**
	 * @param float $value Boost value
	 * @return Elastica_Query_FuzzyLikeThis Query object
	 */
	public function setBoost($value) {
		$this->_boost = (float) $value;
		return $this;
	}


	/**
	 * Set Prefix Length
	 *
	 * @param int $value Prefix length
	 * @return Elastica_Query_FuzzyLikeThis
	 */
	public function setPrefixLength($value) {
		$this->_prefixLength = (int) $value;
		return $this;
	}

	/**
	 * Set max_query_terms
	 *
	 * @param int $value Max query terms value
	 * @return Elastica_Query_FuzzyLikeThis
	 */
	public function setMaxQueryTerms($value) {
		$this->_maxQueryTerms = (int) $value;
		return $this;
	}

	/**
	 * Converts fuzzy like this query to array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {

		if (!empty($this->_fields)) {
			$args['fields'] = $this->_fields;
		}

		if (!empty($this->_boost)) {
			$args['boost'] = $this->_boost;
		}

		if (!empty($this->_likeText)) {
			$args['like_text'] = $this->_likeText;
		}

		$args['min_similarity'] = ($this->_minSimilarity > 0) ? $this->_minSimilarity : 0;

		$args['prefix_length']   = $this->_prefixLength;
		$args['max_query_terms'] = $this->_maxQueryTerms;

		return array('fuzzy_like_this' => $args);
	}
}
