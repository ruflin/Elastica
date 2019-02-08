<?php

namespace Elastica\Query;

use Elastica\Script\AbstractScript;

/**
 * Class FunctionScore.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html
 */
class FunctionScore extends AbstractQuery
{
    const BOOST_MODE_MULTIPLY = 'multiply';
    const BOOST_MODE_REPLACE = 'replace';
    const BOOST_MODE_SUM = 'sum';
    const BOOST_MODE_AVERAGE = 'avg';
    const BOOST_MODE_MAX = 'max';
    const BOOST_MODE_MIN = 'min';

    const SCORE_MODE_MULTIPLY = 'multiply';
    const SCORE_MODE_SUM = 'sum';
    const SCORE_MODE_AVERAGE = 'avg';
    const SCORE_MODE_FIRST = 'first';
    const SCORE_MODE_MAX = 'max';
    const SCORE_MODE_MIN = 'min';

    const DECAY_GAUSS = 'gauss';
    const DECAY_EXPONENTIAL = 'exp';
    const DECAY_LINEAR = 'linear';

    const FIELD_VALUE_FACTOR_MODIFIER_NONE = 'none';
    const FIELD_VALUE_FACTOR_MODIFIER_LOG = 'log';
    const FIELD_VALUE_FACTOR_MODIFIER_LOG1P = 'log1p';
    const FIELD_VALUE_FACTOR_MODIFIER_LOG2P = 'log2p';
    const FIELD_VALUE_FACTOR_MODIFIER_LN = 'ln';
    const FIELD_VALUE_FACTOR_MODIFIER_LN1P = 'ln1p';
    const FIELD_VALUE_FACTOR_MODIFIER_LN2P = 'ln2p';
    const FIELD_VALUE_FACTOR_MODIFIER_SQUARE = 'square';
    const FIELD_VALUE_FACTOR_MODIFIER_SQRT = 'sqrt';
    const FIELD_VALUE_FACTOR_MODIFIER_RECIPROCAL = 'reciprocal';

    const MULTI_VALUE_MODE_MIN = 'min';
    const MULTI_VALUE_MODE_MAX = 'max';
    const MULTI_VALUE_MODE_AVG = 'avg';
    const MULTI_VALUE_MODE_SUM = 'sum';

    const RANDOM_SCORE_FIELD_ID = '_id';
    const RANDOM_SCORE_FIELD_SEQ_NO = '_seq_no';

    protected $_functions = [];

    /**
     * Set the child query for this function_score query.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Add a function to the function_score query.
     *
     * @param string                     $functionType   valid values are DECAY_* constants and script_score
     * @param array|float|AbstractScript $functionParams the body of the function. See documentation for proper syntax.
     * @param AbstractQuery              $filter         filter to apply to the function
     * @param float                      $weight         function weight
     *
     * @return $this
     */
    public function addFunction(
        string $functionType,
        $functionParams,
        AbstractQuery $filter = null,
        float $weight = null
    ): self {
        $function = [
            $functionType => $functionParams,
        ];

        if (null !== $filter) {
            $function['filter'] = $filter;
        }

        if (null !== $weight) {
            $function['weight'] = $weight;
        }

        $this->_functions[] = $function;

        return $this;
    }

    /**
     * Add a script_score function to the query.
     *
     * @param AbstractScript $script a Script object
     * @param AbstractQuery  $filter an optional filter to apply to the function
     * @param float          $weight the weight of the function
     *
     * @return $this
     */
    public function addScriptScoreFunction(AbstractScript $script, AbstractQuery $filter = null, float $weight = null)
    {
        return $this->addFunction('script_score', $script, $filter, $weight);
    }

    /**
     * Add a decay function to the query.
     *
     * @param string        $function       see DECAY_* constants for valid options
     * @param string        $field          the document field on which to perform the decay function
     * @param string        $origin         the origin value for this decay function
     * @param string        $scale          a scale to define the rate of decay for this function
     * @param string        $offset         If defined, this function will only be computed for documents with a distance from the origin greater than this value
     * @param float         $decay          optionally defines how documents are scored at the distance given by the $scale parameter
     * @param float         $weight         optional factor by which to multiply the score at the value provided by the $scale parameter
     * @param AbstractQuery $filter         a filter associated with this function
     * @param string        $multiValueMode see MULTI_VALUE_MODE_* constants for valid options
     *
     * @return $this
     */
    public function addDecayFunction(
        string $function,
        string $field,
        string $origin,
        string $scale,
        string $offset = null,
        float $decay = null,
        float $weight = null,
        AbstractQuery $filter = null,
        string $multiValueMode = null
    ) {
        $functionParams = [
            $field => [
                'origin' => $origin,
                'scale' => $scale,
            ],
        ];
        if (null !== $offset) {
            $functionParams[$field]['offset'] = $offset;
        }
        if (null !== $decay) {
            $functionParams[$field]['decay'] = $decay;
        }

        if (null !== $multiValueMode) {
            $functionParams['multi_value_mode'] = $multiValueMode;
        }

        return $this->addFunction($function, $functionParams, $filter, $weight);
    }

    /**
     * @param string             $field
     * @param float|null         $factor
     * @param string|null        $modifier
     * @param float|null         $missing
     * @param float|null         $weight
     * @param AbstractQuery|null $filter
     *
     * @return $this
     */
    public function addFieldValueFactorFunction(
        string $field,
        float $factor = null,
        string $modifier = null,
        float $missing = null,
        float $weight = null,
        AbstractQuery $filter = null
    ): self {
        $functionParams = [
            'field' => $field,
        ];

        if (null !== $factor) {
            $functionParams['factor'] = $factor;
        }

        if (null !== $modifier) {
            $functionParams['modifier'] = $modifier;
        }

        if (null !== $missing) {
            $functionParams['missing'] = $missing;
        }

        return $this->addFunction('field_value_factor', $functionParams, $filter, $weight);
    }

    /**
     * @param float         $weight the weight of the function
     * @param AbstractQuery $filter a filter associated with this function
     *
     * @return $this
     */
    public function addWeightFunction(float $weight, AbstractQuery $filter = null): self
    {
        return $this->addFunction('weight', $weight, $filter);
    }

    /**
     * Add a random_score function to the query.
     *
     * @param int           $seed   the seed value
     * @param AbstractQuery $filter a filter associated with this function
     * @param float         $weight an optional boost value associated with this function
     * @param string        $field  the field to be used for random number generation
     *
     * @return $this
     */
    public function addRandomScoreFunction(
        int $seed,
        AbstractQuery $filter = null,
        float $weight = null,
        string $field = null
    ): self {
        $functionParams = [
            'seed' => $seed,
        ];

        if (null !== $field) {
            $functionParams['field'] = $field;
        }

        return $this->addFunction('random_score', $functionParams, $filter, $weight);
    }

    /**
     * Set an overall boost value for this query.
     *
     * @param float $boost
     *
     * @return $this
     */
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Restrict the combined boost of the function_score query and its child query.
     *
     * @param float $maxBoost
     *
     * @return $this
     */
    public function setMaxBoost(float $maxBoost): self
    {
        return $this->setParam('max_boost', $maxBoost);
    }

    /**
     * The boost mode determines how the score of this query is combined with that of the child query.
     *
     * @param string $mode see BOOST_MODE_* constants for valid options. Default is multiply.
     *
     * @return $this
     */
    public function setBoostMode(string $mode = self::BOOST_MODE_MULTIPLY): self
    {
        return $this->setParam('boost_mode', $mode);
    }

    /**
     * If set, this query will return results in random order.
     *
     * @param int $seed set a seed value to return results in the same random order for consistent pagination
     *
     * @return $this
     */
    public function setRandomScore(int $seed = null): self
    {
        $seedParam = new \stdClass();
        if (null !== $seed) {
            $seedParam->seed = $seed;
        }

        return $this->setParam('random_score', $seedParam);
    }

    /**
     * Set the score method.
     *
     * @param string $mode see SCORE_MODE_* constants for valid options. Default is multiply.
     *
     * @return $this
     */
    public function setScoreMode(string $mode = self::SCORE_MODE_MULTIPLY): self
    {
        return $this->setParam('score_mode', $mode);
    }

    /**
     * Set min_score option.
     *
     * @param float $minScore
     *
     * @return $this
     */
    public function setMinScore(float $minScore): self
    {
        return $this->setParam('min_score', $minScore);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (0 < \count($this->_functions)) {
            $this->setParam('functions', $this->_functions);
        }

        return parent::toArray();
    }
}
