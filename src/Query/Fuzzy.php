<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Fuzzy query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
 */
class Fuzzy extends AbstractQuery
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
     * Construct a fuzzy query.
     *
     * @param string|null $value String to search for
     */
    public function __construct(?string $fieldName = null, ?string $value = null)
    {
        if (null !== $fieldName && null !== $value) {
            $this->setField($fieldName, $value);
        }
    }

    /**
     * Set field for fuzzy query.
     *
     * @param string $value String to search for
     *
     * @return $this
     */
    public function setField(string $fieldName, string $value): self
    {
        if (\count($this->getParams()) > 0 && \key($this->getParams()) !== $fieldName) {
            throw new InvalidException('Fuzzy query can only support a single field.');
        }

        return $this->setParam($fieldName, ['value' => $value]);
    }

    /**
     * Set optional parameters on the existing query.
     *
     * @param mixed $value Value of the parameter
     *
     * @return $this
     */
    public function setFieldOption(string $option, $value): self
    {
        // Retrieve the single existing field for alteration.
        $params = $this->getParams();
        if (\count($params) < 1) {
            throw new InvalidException('No field has been set');
        }
        $key = \key($params);
        $params[$key][$option] = $value;

        return $this->setParam($key, $params[$key]);
    }

    /**
     * Set the method used to rewrite the query.
     * Use one of the Fuzzy::REWRITE_* constants, or provide your own.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-term-rewrite.html
     */
    public function setRewrite(string $rewriteMode): self
    {
        $this->setFieldOption('rewrite', $rewriteMode);

        return $this;
    }
}
