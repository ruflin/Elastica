<?php

namespace Elastica\Suggest\CandidateGenerator;

/**
 * Class DirectGenerator.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html#_direct_generators
 */
class DirectGenerator extends AbstractCandidateGenerator
{
    public const SUGGEST_MODE_MISSING = 'missing';
    public const SUGGEST_MODE_POPULAR = 'popular';
    public const SUGGEST_MODE_ALWAYS = 'always';

    public const DEFAULT_SIZE = 5;
    public const DEFAULT_SUGGEST_MODE = self::SUGGEST_MODE_MISSING;
    public const DEFAULT_MAX_EDITS = 2;
    public const DEFAULT_PREFIX_LENGTH = 1;
    public const DEFAULT_MIN_WORD_LENGTH = 4;
    public const DEFAULT_MAX_INSPECTIONS = 5;
    public const DEFAULT_MIN_DOC_FREQ = 0.0;
    public const DEFAULT_MAX_TERM_FREQ = 0.01;

    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * Set the field name from which to fetch candidate suggestions.
     *
     * @return $this
     */
    public function setField(string $field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the maximum corrections to be returned per suggest text token.
     *
     * @return $this
     */
    public function setSize(int $size)
    {
        return $this->setParam('size', $size);
    }

    /**
     * @param string $mode see SUGGEST_MODE_* constants for options
     *
     * @return $this
     */
    public function setSuggestMode(string $mode)
    {
        return $this->setParam('suggest_mode', $mode);
    }

    /**
     * @param int $max can only be a value between 1 and 2. Defaults to 2.
     *
     * @return $this
     */
    public function setMaxEdits(int $max)
    {
        return $this->setParam('max_edits', $max);
    }

    /**
     * @param int $length defaults to 1
     *
     * @return $this
     */
    public function setPrefixLength(int $length)
    {
        return $this->setParam('prefix_length', $length);
    }

    /**
     * @param int $min defaults to 4
     *
     * @return $this
     */
    public function setMinWordLength(int $min)
    {
        return $this->setParam('min_word_length', $min);
    }

    /**
     * @return $this
     */
    public function setMaxInspections(int $max)
    {
        return $this->setParam('max_inspections', $max);
    }

    /**
     * @return $this
     */
    public function setMinDocFrequency(float $min)
    {
        return $this->setParam('min_doc_freq', $min);
    }

    /**
     * @return $this
     */
    public function setMaxTermFrequency(float $max)
    {
        return $this->setParam('max_term_freq', $max);
    }

    /**
     * Set an analyzer to be applied to the original token prior to candidate generation.
     *
     * @param string $pre an analyzer
     *
     * @return $this
     */
    public function setPreFilter(string $pre)
    {
        return $this->setParam('pre_filter', $pre);
    }

    /**
     * Set an analyzer to be applied to generated tokens before they are passed to the phrase scorer.
     *
     * @return $this
     */
    public function setPostFilter(string $post)
    {
        return $this->setParam('post_filter', $post);
    }
}
