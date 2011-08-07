<?php
/**
 * More Like This query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Raul Martinez, Jr <juneym@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/mlt-query.html
 */
class Elastica_Query_MoreLikeThis extends Elastica_Query_Abstract
{
	/**
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * @var string
	 */
	protected $_likeText   = null;

	protected $_percTermsToMatch = 0.3;
	protected $_minTermFreq = 2;
	protected $_maxQueryTerms = 25;
	protected $_minDocFreq = 5;
	protected $_maxDocFreq = null;
	protected $_minWordLen = 0;
	protected $_maxWordLen = null;
	protected $_boostTerms = 1;
	protected $_boost = 1.0;

	/**
	 * @var string
	 */
	protected $_analyzer = null;

	/**
	 * @var array
	 */
	protected $_stopWords = null;


	/**
	 * Adds field to flt query
	 *
	 * @param array $fields Field names
	 * @return Elastica_Query_MoreLikeThis Current object
	 */
	public function addFields(Array $fields) {
		$this->_fields = $fields;
		return $this;
	}

	/**
	 * Set the "like_text" value
	 *
	 * @param string $text
	 * @return Elastica_Query_MoreLikeThis This current object
	 */
	public function setLikeText($text) {
		$text = trim($text);
		$this->_likeText = $text;
		return $this;
	}

	/**
	 * @param float $value Boost value
	 * @return Elastica_Query_MoreLikeThis Query object
	 */
	public function setBoost($value) {
		$this->_boost = (float) $value;
		return $this;
	}

	/**
	 * Set max_query_terms
	 *
	 * @param int $value Max query terms value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMaxQueryTerms($value) {
		$this->_maxQueryTerms = (int)$value;
		return $this;
	}


	/**
	 * @param float $perc percentage
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setPercentTermsToMatch( $perc ) {
		$perc= (float) $perc;

		$this->_percTermsToMatch = $perc;

		return $this;
	}

	/**
	 * @param int $value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMinTermFrequency( $value ) {
		$value = (int)$value;
		if ($value < 0) {
			$value = 0;
		}

		$this->_minTermFreq = $value;

		return $this;
	}


	/**
	 * @param int $value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMinDocFrequency( $value ) {
		$value = (int)$value;
		$value = ($value < 0) ?  5 : $value;

		$this->_minDocFreq = $value;
		return $this;
	}

	/**
	 * @param int $value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMaxDocFrequency($value) {
		$value = (int)$value;
		$value = ($value < 0) ?  null : $value;

		$this->_maxDocFreq = $value;
		return $this;
	}


	/**
	 * @param int $value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMinWordLength( $value ) {
		$value = (int)$value;
		$value = ($value <= 0) ?  0 : $value;

		$this->_minWordLen = $value;
		return $this;
	}

	/**
	 * @param int $value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMaxWordLength($value) {
		$value = (int)$value;
		$value = ($value < 0) ?  null : $value;

		$this->_maxWordLen = $value;
		return $this;
	}


	/**
	 * @param int $value
	 * @return Elastica_Query_MoreLikeThis
	 * @link http://www.elasticsearch.org/guide/reference/query-dsl/mlt-query.html
	 */
	public function setBoostTerms($value) {
		$value = (int) $value;
		$value = ($value < 0) ? 1 : $value;
		$this->_boostTerms = $value;
		return $this;
	}


	/**
	 * @param string $value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setAnalyzer( $value ) {
		$value = trim($value);
		if (!empty($value)) {
			$this->_analyzer = $value;
		}

		return $this;
	}


	/**
	 * @param Array $words
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setStopWords(Array $words) {
		$this->_stopWords = $words;
		return $this;
	}

	/**
	 * Converts more_like_this  query to array
	 *
	 * @return Array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {

		if (!empty($this->_fields)) {
			$args['mlt_fields'] = $this->_fields;
		}

		if (!empty($this->_boost)) {
			$args['boost'] = $this->_boost;
		}

		if (!empty($this->_likeText)) {
			$args['like_text'] = $this->_likeText;
		}

		$args['max_query_terms'] = $this->_maxQueryTerms;

		$args['percent_terms_to_match']   = $this->_percTermsToMatch;
		$args['min_term_freq'] = $this->_minTermFreq;

		if (!empty($this->_stopWords)) {
			$args['stop_words'] = $this->_stopWords;
		}

		if (!empty($this->_analyzer)) {
			$args['analyzer'] = $this->_analyzer;
		}

		$args['min_doc_freq'] = $this->_minDocFreq;

		if ($this->_maxDocFreq != null) {
			$args['max_doc_freq'] = $this->_maxDocFreq;
		}

		$args['min_word_len'] = $this->_minWordLen;
		if ($this->_maxWordLen != null) {
			$args['max_word_len'] = $this->_maxWordLen;
		}
		$args['boost_terms']  = $this->_boostTerms;

		return array('mlt' => $args);
	}
}
