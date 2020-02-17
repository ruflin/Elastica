<?php

namespace Elastica\Query;

/**
 * More Like This query.
 *
 * @author Raul Martinez, Jr <juneym@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html
 */
class MoreLikeThis extends AbstractQuery
{
    /**
     * Set fields to which to restrict the mlt query.
     *
     * @param array $fields Field names
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Set the "like" value.
     *
     * @param self|string $like
     *
     * @return $this
     */
    public function setLike($like): self
    {
        return $this->setParam('like', $like);
    }

    /**
     * Set boost.
     *
     * @param float $boost Boost value
     *
     * @return $this
     */
    public function setBoost(float $boost = 1.0): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Set max_query_terms.
     *
     * @param int $maxQueryTerms Max query terms value
     *
     * @return $this
     */
    public function setMaxQueryTerms(int $maxQueryTerms = 25): self
    {
        return $this->setParam('max_query_terms', $maxQueryTerms);
    }

    /**
     * Set min term frequency.
     *
     * @return $this
     */
    public function setMinTermFrequency(int $minTermFreq = 2): self
    {
        return $this->setParam('min_term_freq', $minTermFreq);
    }

    /**
     * set min document frequency.
     *
     * @return $this
     */
    public function setMinDocFrequency(int $minDocFreq = 5): self
    {
        return $this->setParam('min_doc_freq', $minDocFreq);
    }

    /**
     * set max document frequency.
     *
     * @return $this
     */
    public function setMaxDocFrequency(int $maxDocFreq = 0): self
    {
        return $this->setParam('max_doc_freq', $maxDocFreq);
    }

    /**
     * Set min word length.
     *
     * @return $this
     */
    public function setMinWordLength(int $minWordLength = 0): self
    {
        return $this->setParam('min_word_length', $minWordLength);
    }

    /**
     * Set max word length.
     *
     * @return $this
     */
    public function setMaxWordLength(int $maxWordLength = 0): self
    {
        return $this->setParam('max_word_length', $maxWordLength);
    }

    /**
     * Set boost terms.
     *
     * @return $this
     */
    public function setBoostTerms(bool $boostTerms = false): self
    {
        return $this->setParam('boost_terms', $boostTerms);
    }

    /**
     * Set analyzer.
     *
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        $analyzer = \trim($analyzer);

        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Set stop words.
     *
     * @return $this
     */
    public function setStopWords(array $stopWords): self
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
    public function setMinimumShouldMatch($minimumShouldMatch = '30%'): self
    {
        return $this->setParam('minimum_should_match', $minimumShouldMatch);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // If _id is provided, perform MLT on an existing document from the index
        // If _source is provided, perform MLT on a document provided as an input
        if (!empty($array['more_like_this']['like']['_id'])) {
            $doc = $array['more_like_this']['like'];
            $doc = \array_intersect_key($doc, ['_index' => 1, '_type' => 1, '_id' => 1]);
            $array['more_like_this']['like'] = $doc;
        } elseif (!empty($array['more_like_this']['like']['_source'])) {
            $doc = $array['more_like_this']['like'];
            $doc['doc'] = $array['more_like_this']['like']['_source'];
            unset($doc['_id'], $doc['_source']);

            $array['more_like_this']['like'] = $doc;
        }

        return $array;
    }
}
