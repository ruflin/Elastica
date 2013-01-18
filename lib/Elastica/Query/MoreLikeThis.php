<?php

namespace Elastica\Query;

/**
 * More Like This query
 *
 * @category Xodoa
 * @package Elastica
 * @author Raul Martinez, Jr <juneym@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/mlt-query.html
 */
class MoreLikeThis extends AbstractQuery
{
    /**
     * Adds field to mlt query
     *
     * @param  array                            $fields Field names
     * @return \Elastica\Query\MoreLikeThis Current object
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Set the "like_text" value
     *
     * @param  string                           $likeText
     * @return \Elastica\Query\MoreLikeThis This current object
     */
    public function setLikeText($likeText)
    {
        $likeText = trim($likeText);

        return $this->setParam('like_text', $likeText);
    }

    /**
     * Set boost
     *
     * @param  float                            $boost Boost value
     * @return \Elastica\Query\MoreLikeThis Query object
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', (float) $boost);
    }

    /**
     * Set max_query_terms
     *
     * @param  int                              $maxQueryTerms Max query terms value
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setMaxQueryTerms($maxQueryTerms)
    {
        return $this->setParam('max_query_terms', (int) $maxQueryTerms);
    }

    /**
     * Set percent terms to match
     *
     * @param  float                            $percentTermsToMatch Percentage
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setPercentTermsToMatch($percentTermsToMatch)
    {
        return $this->setParam('percent_terms_to_match', (float) $percentTermsToMatch);
    }

    /**
     * Set min term frequency
     *
     * @param  int                              $minTermFreq
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setMinTermFrequency($minTermFreq)
    {
        return $this->setParam('min_term_freq', (int) $minTermFreq);
    }

    /**
     * set min document frequency
     *
     * @param  int                              $minDocFreq
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setMinDocFrequency($minDocFreq)
    {
        return $this->setParam('min_doc_freq', (int) $minDocFreq);
    }

    /**
     * set max document frequency
     *
     * @param  int                              $maxDocFreq
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setMaxDocFrequency($maxDocFreq)
    {
        return $this->setParam('max_doc_freq', (int) $maxDocFreq);
    }

    /**
     * Set min word length
     *
     * @param  int                              $minWordLength
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setMinWordLength($minWordLength)
    {
        return $this->setParam('min_word_length', (int) $minWordLength);
    }

    /**
     * Set max word length
     *
     * @param  int                              $maxWordLength
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setMaxWordLength($maxWordLength)
    {
        return $this->setParam('max_word_length', (int) $maxWordLength);
    }

    /**
     * Set boost terms
     *
     * @param  bool                             $boostTerms
     * @return \Elastica\Query\MoreLikeThis
     * @link http://www.elasticsearch.org/guide/reference/query-dsl/mlt-query.html
     */
    public function setBoostTerms($boostTerms)
    {
        return $this->setParam('boost_terms', (bool) $boostTerms);
    }

    /**
     * Set analyzer
     *
     * @param  string                           $analyzer
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setAnalyzer($analyzer)
    {
        $analyzer = trim($analyzer);

        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Set stop words
     *
     * @param  array                            $stopWords
     * @return \Elastica\Query\MoreLikeThis
     */
    public function setStopWords(array $stopWords)
    {
        return $this->setParam('stop_words', $stopWords);
    }
}
