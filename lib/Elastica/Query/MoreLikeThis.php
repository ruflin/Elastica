<?php
namespace Elastica\Query;

/**
 * More Like This query.
 *
 * @author Raul Martinez, Jr <juneym@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html
 */
class MoreLikeThis extends AbstractQuery
{
    /**
     * Set fields to which to restrict the mlt query.
     *
     * @param array $fields Field names
     *
     * @return \Elastica\Query\MoreLikeThis Current object
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Set document ids for the mlt query.
     *
     * @param array $ids Document ids
     *
     * @return \Elastica\Query\MoreLikeThis Current object
     */
    public function setIds(array $ids)
    {
        return $this->setParam('ids', $ids);
    }

    /**
     * Set the "like_text" value.
     *
     * @param string $likeText
     *
     * @return $this
     */
    public function setLikeText($likeText)
    {
        $likeText = trim($likeText);

        return $this->setParam('like_text', $likeText);
    }

    /**
     * Set boost.
     *
     * @param float $boost Boost value
     *
     * @return $this
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', (float) $boost);
    }

    /**
     * Set max_query_terms.
     *
     * @param int $maxQueryTerms Max query terms value
     *
     * @return $this
     */
    public function setMaxQueryTerms($maxQueryTerms)
    {
        return $this->setParam('max_query_terms', (int) $maxQueryTerms);
    }

    /**
     * Set percent terms to match.
     *
     * @param float $percentTermsToMatch Percentage
     *
     * @return $this
     *
     * @deprecated Option "percent_terms_to_match" deprecated as of ES 1.5. Use "minimum_should_match" instead.
     */
    public function setPercentTermsToMatch($percentTermsToMatch)
    {
        return $this->setParam('percent_terms_to_match', (float) $percentTermsToMatch);
    }

    /**
     * Set min term frequency.
     *
     * @param int $minTermFreq
     *
     * @return $this
     */
    public function setMinTermFrequency($minTermFreq)
    {
        return $this->setParam('min_term_freq', (int) $minTermFreq);
    }

    /**
     * set min document frequency.
     *
     * @param int $minDocFreq
     *
     * @return $this
     */
    public function setMinDocFrequency($minDocFreq)
    {
        return $this->setParam('min_doc_freq', (int) $minDocFreq);
    }

    /**
     * set max document frequency.
     *
     * @param int $maxDocFreq
     *
     * @return $this
     */
    public function setMaxDocFrequency($maxDocFreq)
    {
        return $this->setParam('max_doc_freq', (int) $maxDocFreq);
    }

    /**
     * Set min word length.
     *
     * @param int $minWordLength
     *
     * @return $this
     */
    public function setMinWordLength($minWordLength)
    {
        return $this->setParam('min_word_length', (int) $minWordLength);
    }

    /**
     * Set max word length.
     *
     * @param int $maxWordLength
     *
     * @return $this
     */
    public function setMaxWordLength($maxWordLength)
    {
        return $this->setParam('max_word_length', (int) $maxWordLength);
    }

    /**
     * Set boost terms.
     *
     * @param bool $boostTerms
     *
     * @return $this
     */
    public function setBoostTerms($boostTerms)
    {
        return $this->setParam('boost_terms', (bool) $boostTerms);
    }

    /**
     * Set analyzer.
     *
     * @param string $analyzer
     *
     * @return $this
     */
    public function setAnalyzer($analyzer)
    {
        $analyzer = trim($analyzer);

        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Set stop words.
     *
     * @param array $stopWords
     *
     * @return $this
     */
    public function setStopWords(array $stopWords)
    {
        return $this->setParam('stop_words', $stopWords);
    }

    /**
     * Set minimum_should_match option.
     *
     * @param int|string $minimumShouldMatch
     *
     * @return $this
     */
    public function setMinimumShouldMatch($minimumShouldMatch)
    {
        return $this->setParam('minimum_should_match', $minimumShouldMatch);
    }
}
