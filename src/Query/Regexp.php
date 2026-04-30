<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Regexp query.
 *
 * @author Aurélien Le Grand <gnitg@yahoo.fr>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
 */
class Regexp extends AbstractQuery
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
     * Construct regexp query.
     *
     * @param string      $key   OPTIONAL Regexp key
     * @param string|null $value OPTIONAL Regexp value
     * @param float       $boost OPTIONAL Boost value (default = 1)
     */
    public function __construct(string $key = '', ?string $value = null, float $boost = 1.0)
    {
        if ('' !== $key) {
            $this->setValue($key, $value, $boost);
        }
    }

    /**
     * Sets the query expression for a key with its boost value.
     *
     * @return $this
     */
    public function setValue(string $key, ?string $value = null, float $boost = 1.0): self
    {
        $this->field = $key;

        return $this->setParam($key, ['value' => $value, 'boost' => $boost]);
    }

    /**
     * Set the method used to rewrite the query.
     * Use one of the Regexp::REWRITE_* constants, or provide your own.
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
