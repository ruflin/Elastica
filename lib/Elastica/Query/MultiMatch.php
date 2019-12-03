<?php

namespace Elastica\Query;

/**
 * Multi Match.
 *
 * @author Rodolfo Adhenawer Campagnoli Moraes <adhenawer@gmail.com>
 * @author Wong Wing Lun <luiges90@gmail.com>
 * @author Tristan Maindron <tmaindron@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html
 */
class MultiMatch extends AbstractQuery
{
    public const TYPE_BEST_FIELDS = 'best_fields';
    public const TYPE_MOST_FIELDS = 'most_fields';
    public const TYPE_CROSS_FIELDS = 'cross_fields';
    public const TYPE_PHRASE = 'phrase';
    public const TYPE_PHRASE_PREFIX = 'phrase_prefix';

    public const OPERATOR_OR = 'or';
    public const OPERATOR_AND = 'and';

    public const ZERO_TERM_NONE = 'none';
    public const ZERO_TERM_ALL = 'all';

    public const FUZZINESS_AUTO = 'AUTO';

    /**
     * Sets the query.
     *
     * @param string $query Query
     *
     * @return $this
     */
    public function setQuery(string $query = ''): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Sets Fields to be used in the query.
     *
     * @param array $fields Fields
     *
     * @return $this
     */
    public function setFields(array $fields = []): self
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets use dis max indicating to either create a dis_max query or a bool query.
     *
     * If not set, defaults to true.
     *
     * @return $this
     */
    public function setUseDisMax(bool $useDisMax = true): self
    {
        return $this->setParam('use_dis_max', $useDisMax);
    }

    /**
     * Sets tie breaker to multiplier value to balance the scores between lower and higher scoring fields.
     *
     * If not set, defaults to 0.0.
     *
     * @return $this
     */
    public function setTieBreaker(float $tieBreaker = 0.0): self
    {
        return $this->setParam('tie_breaker', $tieBreaker);
    }

    /**
     * Sets operator for Match Query.
     *
     * If not set, defaults to 'or'
     *
     * @return $this
     */
    public function setOperator(string $operator = self::OPERATOR_OR): self
    {
        return $this->setParam('operator', $operator);
    }

    /**
     * Set field minimum should match for Match Query.
     *
     * @param mixed $minimumShouldMatch
     *
     * @return $this
     */
    public function setMinimumShouldMatch($minimumShouldMatch): self
    {
        return $this->setParam('minimum_should_match', $minimumShouldMatch);
    }

    /**
     * Set zero terms query for Match Query.
     *
     * If not set, default to 'none'
     *
     * @return $this
     */
    public function setZeroTermsQuery(string $zeroTermQuery = self::ZERO_TERM_NONE): self
    {
        return $this->setParam('zero_terms_query', $zeroTermQuery);
    }

    /**
     * Set cutoff frequency for Match Query.
     *
     * @return $this
     */
    public function setCutoffFrequency(float $cutoffFrequency): self
    {
        return $this->setParam('cutoff_frequency', $cutoffFrequency);
    }

    /**
     * Set type.
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        return $this->setParam('type', $type);
    }

    /**
     * Set fuzziness.
     *
     * @param float|string $fuzziness
     *
     * @return $this
     */
    public function setFuzziness($fuzziness): self
    {
        return $this->setParam('fuzziness', $fuzziness);
    }

    /**
     * Set prefix length.
     *
     * @return $this
     */
    public function setPrefixLength(int $prefixLength): self
    {
        return $this->setParam('prefix_length', $prefixLength);
    }

    /**
     * Set max expansions.
     *
     * @return $this
     */
    public function setMaxExpansions(int $maxExpansions): self
    {
        return $this->setParam('max_expansions', $maxExpansions);
    }

    /**
     * Set analyzer.
     *
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        return $this->setParam('analyzer', $analyzer);
    }
}
