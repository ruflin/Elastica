<?php
namespace Elastica\Suggest;

/**
 * Class Term.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-term.html
 */
class Term extends AbstractSuggest
{
    const SORT_SCORE = 'score';
    const SORT_FREQUENCY = 'frequency';

    const SUGGEST_MODE_MISSING = 'missing';
    const SUGGEST_MODE_POPULAR = 'popular';
    const SUGGEST_MODE_ALWAYS = 'always';

    /**
     * @param string $analyzer
     *
     * @return $this
     */
    public function setAnalyzer($analyzer)
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * @param string $sort see SORT_* constants for options
     *
     * @return $this
     */
    public function setSort($sort)
    {
        return $this->setParam('sort', $sort);
    }

    /**
     * @param string $mode see SUGGEST_MODE_* constants for options
     *
     * @return $this
     */
    public function setSuggestMode($mode)
    {
        return $this->setParam('suggest_mode', $mode);
    }

    /**
     * If true, suggest terms will be lower cased after text analysis.
     *
     * @param bool $lowercase
     *
     * @return $this
     */
    public function setLowercaseTerms($lowercase = true)
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
    public function setMaxEdits($max)
    {
        return $this->setParam('max_edits', (int) $max);
    }

    /**
     * The number of minimum prefix characters that must match in order to be a suggestion candidate.
     *
     * @param int $length Defaults to 1.
     *
     * @return $this
     */
    public function setPrefixLength($length)
    {
        return $this->setParam('prefix_len', (int) $length);
    }

    /**
     * The minimum length a suggest text term must have in order to be included.
     *
     * @param int $length Defaults to 4.
     *
     * @return $this
     */
    public function setMinWordLength($length)
    {
        return $this->setParam('min_word_len', (int) $length);
    }

    /**
     * @param int $max Defaults to 5.
     *
     * @return $this
     */
    public function setMaxInspections($max)
    {
        return $this->setParam('max_inspections', $max);
    }

    /**
     * Set the minimum number of documents in which a suggestion should appear.
     *
     * @param int|float $min Defaults to 0. If the value is greater than 1, it must be a whole number.
     *
     * @return $this
     */
    public function setMinDocFrequency($min)
    {
        return $this->setParam('min_doc_freq', $min);
    }

    /**
     * Set the maximum number of documents in which a suggest text token can exist in order to be included.
     *
     * @param float $max
     *
     * @return $this
     */
    public function setMaxTermFrequency($max)
    {
        return $this->setParam('max_term_freq', $max);
    }
}
