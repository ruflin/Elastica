<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Prefix query.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
 */
class Prefix extends AbstractQuery
{
    /**
     * Rewrite methods: @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-term-rewrite.html.
     */
    public const REWRITE_CONSTANT_SCORE = 'constant_score';
    public const REWRITE_CONSTANT_SCORE_BOOLEAN = 'constant_score_boolean';
    public const REWRITE_SCORING_BOOLEAN = 'scoring_boolean';
    public const REWRITE_TOP_TERMS_BLENDED_FREQS_N = 'top_terms_blended_freqs_N';
    public const REWRITE_TOP_TERMS_BOOST_N = 'top_terms_boost_N';
    public const REWRITE_TOP_TERMS_N = 'top_terms_N';

    /**
     * @var string|null
     */
    private $field;

    /**
     * @param array $prefix OPTIONAL Calls setRawPrefix with the given $prefix array
     */
    public function __construct(array $prefix = [])
    {
        $this->setRawPrefix($prefix);
    }

    /**
     * setRawPrefix can be used instead of setPrefix if some more special
     * values for a prefix have to be set.
     *
     * @param array $prefix Prefix array
     *
     * @return $this
     */
    public function setRawPrefix(array $prefix): self
    {
        $this->field = \array_key_first($prefix);

        return $this->setParams($prefix);
    }

    /**
     * Adds a prefix to the prefix query.
     *
     * @param string       $key   Key to query
     * @param array|string $value Values(s) for the query. Boost can be set with array
     * @param float        $boost OPTIONAL Boost value (default = 1.0)
     *
     * @return $this
     */
    public function setPrefix(string $key, $value, float $boost = 1.0): self
    {
        return $this->setRawPrefix([$key => ['value' => $value, 'boost' => $boost]]);
    }

    /**
     * Set the method used to rewrite the query.
     * Use one of the Prefix::REWRITE_* constants, or provide your own.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-term-rewrite.html
     */
    public function setRewrite(string $rewriteMode): self
    {
        if (null === $this->field) {
            throw new InvalidException('No field has been set');
        }

        $data = $this->getParam($this->field);
        $this->setParam($this->field, \array_merge($data, ['rewrite' => $rewriteMode]));

        return $this;
    }
}
