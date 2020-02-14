<?php

namespace Elastica\Suggest;

/**
 * Class Term.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-term.html
 */
class Term extends AbstractSuggest
{
    public const SORT_SCORE = 'score';
    public const SORT_FREQUENCY = 'frequency';

    public const SUGGEST_MODE_MISSING = 'missing';
    public const SUGGEST_MODE_POPULAR = 'popular';
    public const SUGGEST_MODE_ALWAYS = 'always';

    /**
     * @return $this
     */
    public function setAnalyzer(string $analyzer): Term
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * @param string $sort see SORT_* constants for options
     *
     * @return $this
     */
    public function setSort(string $sort): Term
    {
        return $this->setParam('sort', $sort);
    }

    /**
     * @param string $mode see SUGGEST_MODE_* constants for options
     *
     * @return $this
     */
    public function setSuggestMode(string $mode): Term
    {
        return $this->setParam('suggest_mode', $mode);
    }

    /**
     * If true, suggest terms will be lower cased after text analysis.
     *
     * @return $this
     */
    public function setLowercaseTerms(bool $lowercase = true): Term
    {
        return $this->setParam('lowercase_terms', (bool) $lowercase);
    }

    /**
     * Set the maximum edit distance candidate suggestions can have in order to be considered as a suggestion.
     *
     * @param int $max Either 1 or 2. Any other value will result in an error.
     *
     * @return $this
     */
    public function setMaxEdits(int $max = 2): Term
    {
        return $this->setParam('max_edits', $max);
    }

    /**
     * The number of minimum prefix characters that must match in order to be a suggestion candidate.
     *
     * @param int $length defaults to 1
     *
     * @return $this
     */
    public function setPrefixLength(int $length = 1): Term
    {
        return $this->setParam('prefix_length', $length);
    }

    /**
     * The minimum length a suggest text term must have in order to be included.
     *
     * @param int $length defaults to 4
     *
     * @return $this
     */
    public function setMinWordLength(int $length = 4): Term
    {
        return $this->setParam('min_word_length', $length);
    }

    /**
     * @param int $max defaults to 5
     *
     * @return $this
     */
    public function setMaxInspections(int $max = 5): Term
    {
        return $this->setParam('max_inspections', $max);
    }

    /**
     * Set the minimum number of documents in which a suggestion should appear.
     *
     * @param float|int $min Defaults to 0. If the value is greater than 1, it must be a whole number.
     *
     * @return $this
     */
    public function setMinDocFrequency(float $min = 0): Term
    {
        return $this->setParam('min_doc_freq', $min);
    }

    /**
     * Set the maximum number of documents in which a suggest text token can exist in order to be included.
     *
     * @return $this
     */
    public function setMaxTermFrequency(float $max = 0.01): Term
    {
        return $this->setParam('max_term_freq', $max);
    }

    /**
     * Which string distance implementation to use for comparing how similar suggested terms are.
     * Five possible values can be specified:.
     *
     * - internal
     * - damerau_levenshtein
     * - levenshtein
     * - jaro_winkler
     * - ngram
     *
     * @return $this
     */
    public function setStringDistanceAlgorithm(string $distanceAlgorithm): Term
    {
        return $this->setParam('string_distance', $distanceAlgorithm);
    }
}
