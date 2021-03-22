<?php

namespace Elastica\Query;

/**
 * Match query.
 *
 * @author F21
 * @author WONG Wing Lun <luiges90@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
 */
class MatchQuery extends AbstractQuery
{
    public const OPERATOR_OR = 'or';
    public const OPERATOR_AND = 'and';

    public const ZERO_TERM_NONE = 'none';
    public const ZERO_TERM_ALL = 'all';

    public const FUZZINESS_AUTO = 'AUTO';

    /**
     * @param string $field
     * @param mixed  $values
     */
    public function __construct(?string $field = null, $values = null)
    {
        if (null !== $field && null !== $values) {
            $this->setParam($field, $values);
        }
    }

    /**
     * Sets a param for the message array.
     *
     * @param mixed $values
     *
     * @return $this
     */
    public function setField(string $field, $values): self
    {
        return $this->setParam($field, $values);
    }

    /**
     * Sets a param for the given field.
     *
     * @param bool|float|int|string $value
     *
     * @return $this
     */
    public function setFieldParam(string $field, string $key, $value): self
    {
        if (!isset($this->_params[$field])) {
            $this->_params[$field] = [];
        }

        $this->_params[$field][$key] = $value;

        return $this;
    }

    /**
     * Sets the query string.
     *
     * @return $this
     */
    public function setFieldQuery(string $field, string $query): self
    {
        return $this->setFieldParam($field, 'query', $query);
    }

    /**
     * Set field operator.
     *
     * @return $this
     */
    public function setFieldOperator(string $field, string $operator = self::OPERATOR_OR): self
    {
        return $this->setFieldParam($field, 'operator', $operator);
    }

    /**
     * Set field analyzer.
     *
     * @return $this
     */
    public function setFieldAnalyzer(string $field, string $analyzer): self
    {
        return $this->setFieldParam($field, 'analyzer', $analyzer);
    }

    /**
     * Set field boost value.
     *
     * If not set, defaults to 1.0.
     *
     * @return $this
     */
    public function setFieldBoost(string $field, float $boost = 1.0): self
    {
        return $this->setFieldParam($field, 'boost', $boost);
    }

    /**
     * Set field minimum should match.
     *
     * @param int|string $minimumShouldMatch
     *
     * @return $this
     *
     * @see Possible values for minimum_should_match https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html
     */
    public function setFieldMinimumShouldMatch(string $field, $minimumShouldMatch): self
    {
        return $this->setFieldParam($field, 'minimum_should_match', $minimumShouldMatch);
    }

    /**
     * Set field fuzziness.
     *
     * @param mixed $fuzziness
     *
     * @return $this
     */
    public function setFieldFuzziness(string $field, $fuzziness): self
    {
        return $this->setFieldParam($field, 'fuzziness', $fuzziness);
    }

    /**
     * Set field fuzzy rewrite.
     *
     * @return $this
     */
    public function setFieldFuzzyRewrite(string $field, string $fuzzyRewrite): self
    {
        return $this->setFieldParam($field, 'fuzzy_rewrite', $fuzzyRewrite);
    }

    /**
     * Set field prefix length.
     *
     * @return $this
     */
    public function setFieldPrefixLength(string $field, int $prefixLength): self
    {
        return $this->setFieldParam($field, 'prefix_length', $prefixLength);
    }

    /**
     * Set field max expansions.
     *
     * @return $this
     */
    public function setFieldMaxExpansions(string $field, int $maxExpansions): self
    {
        return $this->setFieldParam($field, 'max_expansions', $maxExpansions);
    }

    /**
     * Set zero terms query.
     *
     * If not set, default to 'none'
     *
     * @return $this
     */
    public function setFieldZeroTermsQuery(string $field, string $zeroTermQuery = self::ZERO_TERM_NONE): self
    {
        return $this->setFieldParam($field, 'zero_terms_query', $zeroTermQuery);
    }

    /**
     * Set cutoff frequency.
     *
     * @return $this
     */
    public function setFieldCutoffFrequency(string $field, float $cutoffFrequency): self
    {
        return $this->setFieldParam($field, 'cutoff_frequency', $cutoffFrequency);
    }
}
