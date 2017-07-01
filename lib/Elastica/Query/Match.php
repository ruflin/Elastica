<?php
namespace Elastica\Query;

/**
 * Match query.
 *
 * @author F21
 * @author WONG Wing Lun <luiges90@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
 */
class Match extends AbstractQuery
{
    const OPERATOR_OR = 'or';
    const OPERATOR_AND = 'and';

    const ZERO_TERM_NONE = 'none';
    const ZERO_TERM_ALL = 'all';

    const FUZZINESS_AUTO = 'AUTO';

    /**
     * @param string $field
     * @param mixed  $values
     */
    public function __construct($field = null, $values = null)
    {
        if ($field !== null && $values !== null) {
            $this->setParam($field, $values);
        }
    }

    /**
     * Sets a param for the message array.
     *
     * @param string $field
     * @param mixed  $values
     *
     * @return $this
     */
    public function setField($field, $values)
    {
        return $this->setParam($field, $values);
    }

    /**
     * Sets a param for the given field.
     *
     * @param string $field
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setFieldParam($field, $key, $value)
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
     * @param string $field
     * @param string $query
     *
     * @return $this
     */
    public function setFieldQuery($field, $query)
    {
        return $this->setFieldParam($field, 'query', $query);
    }

    /**
     * Set field type.
     *
     * @param string $field
     * @param string $type
     *
     * @return $this
     *
     * @deprecated Replaced by Elastica\Query\MatchPhrase and Elastica\Query\MatchPhrasePrefix
     */
    public function setFieldType($field, $type)
    {
        trigger_error('Deprecated: Elastica\Query\Match::setFieldType() is deprecated and will be removed in further Elastica releases. Use Elastica\Query\MatchPhrase and Elastica\Query\MatchPhrasePrefix instead.', E_USER_DEPRECATED);

        return $this->setFieldParam($field, 'type', $type);
    }

    /**
     * Set field operator.
     *
     * @param string $field
     * @param string $operator
     *
     * @return $this
     */
    public function setFieldOperator($field, $operator = self::OPERATOR_OR)
    {
        return $this->setFieldParam($field, 'operator', $operator);
    }

    /**
     * Set field analyzer.
     *
     * @param string $field
     * @param string $analyzer
     *
     * @return $this
     */
    public function setFieldAnalyzer($field, $analyzer)
    {
        return $this->setFieldParam($field, 'analyzer', $analyzer);
    }

    /**
     * Set field boost value.
     *
     * If not set, defaults to 1.0.
     *
     * @param string $field
     * @param float  $boost
     *
     * @return $this
     */
    public function setFieldBoost($field, $boost = 1.0)
    {
        return $this->setFieldParam($field, 'boost', (float) $boost);
    }

    /**
     * Set field minimum should match.
     *
     * @param string     $field
     * @param int|string $minimumShouldMatch
     *
     * @return $this
     *
     * @link Possible values for minimum_should_match https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html
     */
    public function setFieldMinimumShouldMatch($field, $minimumShouldMatch)
    {
        return $this->setFieldParam($field, 'minimum_should_match', $minimumShouldMatch);
    }

    /**
     * Set field fuzziness.
     *
     * @param string $field
     * @param mixed  $fuzziness
     *
     * @return $this
     */
    public function setFieldFuzziness($field, $fuzziness)
    {
        return $this->setFieldParam($field, 'fuzziness', $fuzziness);
    }

    /**
     * Set field fuzzy rewrite.
     *
     * @param string $field
     * @param string $fuzzyRewrite
     *
     * @return $this
     */
    public function setFieldFuzzyRewrite($field, $fuzzyRewrite)
    {
        return $this->setFieldParam($field, 'fuzzy_rewrite', $fuzzyRewrite);
    }

    /**
     * Set field prefix length.
     *
     * @param string $field
     * @param int    $prefixLength
     *
     * @return $this
     */
    public function setFieldPrefixLength($field, $prefixLength)
    {
        return $this->setFieldParam($field, 'prefix_length', (int) $prefixLength);
    }

    /**
     * Set field max expansions.
     *
     * @param string $field
     * @param int    $maxExpansions
     *
     * @return $this
     */
    public function setFieldMaxExpansions($field, $maxExpansions)
    {
        return $this->setFieldParam($field, 'max_expansions', (int) $maxExpansions);
    }

    /**
     * Set zero terms query.
     *
     * If not set, default to 'none'
     *
     * @param string $field
     * @param string $zeroTermQuery
     *
     * @return $this
     */
    public function setFieldZeroTermsQuery($field, $zeroTermQuery = self::ZERO_TERM_NONE)
    {
        return $this->setFieldParam($field, 'zero_terms_query', $zeroTermQuery);
    }

    /**
     * Set cutoff frequency.
     *
     * @param string $field
     * @param float  $cutoffFrequency
     *
     * @return $this
     */
    public function setFieldCutoffFrequency($field, $cutoffFrequency)
    {
        return $this->setFieldParam($field, 'cutoff_frequency', $cutoffFrequency);
    }
}
