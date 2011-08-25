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
	 * Adds field to mlt query
	 *
	 * @param array $fields Field names
	 * @return Elastica_Query_MoreLikeThis Current object
	 */
	public function setFields(array $fields) {
		return $this->setParam('mlt_fields', $fields);
	}

	/**
	 * Set the "like_text" value
	 *
	 * @param string $likeText
	 * @return Elastica_Query_MoreLikeThis This current object
	 */
	public function setLikeText($likeText) {
		$likeText = trim($likeText);
		return $this->setParam('like_text', $likeText);
	}

	/**
	 * @param float $boost Boost value
	 * @return Elastica_Query_MoreLikeThis Query object
	 */
	public function setBoost($boost) {
		return $this->setParam('boost', (float) $boost);
	}

	/**
	 * Set max_query_terms
	 *
	 * @param int $maxQueryTerms Max query terms value
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMaxQueryTerms($maxQueryTerms) {
		return $this->setParam('max_query_terms', (int) $maxQueryTerms);
	}


	/**
	 * @param float $percentTermsToMatch Percentage
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setPercentTermsToMatch($percentTermsToMatch) {
		return $this->setParam('percent_terms_to_match', (float) $percentTermsToMatch);
	}

	/**
	 * @param int $minTermFreq
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMinTermFrequency($minTermFreq) {
		return $this->setParam('min_term_freq', (int) $minTermFreq);
	}


	/**
	 * @param int $minDocFreq
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMinDocFrequency($minDocFreq) {
		return $this->setParam('min_doc_freq', (int) $minDocFreq);
	}

	/**
	 * @param int $maxDocFreq
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMaxDocFrequency($maxDocFreq) {
		return $this->setParam('max_doc_freq', (int) $maxDocFreq);
	}


	/**
	 * @param int $minWordLength
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMinWordLength($minWordLength) {
		return $this->setParam('min_word_length', (int) $minWordLength);
	}

	/**
	 * @param int $maxWordLength
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setMaxWordLength($maxWordLength) {
		return $this->setParam('max_word_length', (int) $maxWordLength);
	}

	/**
	 * @param bool $boostTerms
	 * @return Elastica_Query_MoreLikeThis
	 * @link http://www.elasticsearch.org/guide/reference/query-dsl/mlt-query.html
	 */
	public function setBoostTerms($boostTerms) {
		return $this->setParam('boost_terms', (bool) $boostTerms);
	}

	/**
	 * @param string $analyzer
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setAnalyzer($analyzer) {
		$analyzer = trim($analyzer);
		return $this->setParam('analyzer', $analyzer);
	}

	/**
	 * @param array $stopWords
	 * @return Elastica_Query_MoreLikeThis
	 */
	public function setStopWords(array $stopWords) {
		return $this->setParam('stop_words', $stopWords);
	}
}
