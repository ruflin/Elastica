<?php

namespace Elastica\Query;
use Elastica\Filter\AbstractFilter;
use Elastica\Script;

/**
 * Class FunctionScore
 * @package Elastica\Query
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/function-score-query/
 */
class FunctionScore extends AbstractQuery
{
    const BOOST_MODE_MULTIPLY = 'multiply';
    const BOOST_MODE_REPLACE = 'replace';
    const BOOST_MODE_SUM = 'sum';
    const BOOST_MODE_AVERAGE = 'average';
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

    protected $_functions = array();

    /**
     * Set the child query for this function_score query
     * @param AbstractQuery $query
     * @return \Elastica\Query\FunctionScore
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query->toArray());
    }

    /**
     * @param AbstractFilter $filter
     * @return \Elastica\Param
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * Add a function to the function_score query
     * @param string $functionType valid values are DECAY_* constants and script_score
     * @param array|float $functionParams the body of the function. See documentation for proper syntax.
     * @param AbstractFilter $filter optional filter to apply to the function
     * @return \Elastica\Query\FunctionScore
     */
    public function addFunction($functionType, $functionParams, AbstractFilter $filter = NULL)
    {
        $function = array(
            $functionType => $functionParams
        );
        if (!is_null($filter)) {
            $function['filter'] = $filter->toArray();
        }
        $this->_functions[] = $function;
        return $this;
    }

    /**
     * Add a script_score function to the query
     * @param Script $script a Script object
     * @param AbstractFilter $filter an optional filter to apply to the function
     * @return \Elastica\Query\FunctionScore
     */
    public function addScriptScoreFunction(Script $script, AbstractFilter $filter = NULL)
    {
        return $this->addFunction('script_score', $script->toArray(), $filter);
    }

    /**
     * Add a decay function to the query
     * @param string $function see DECAY_* constants for valid options
     * @param string $field the document field on which to perform the decay function
     * @param string $origin the origin value for this decay function
     * @param string $scale a scale to define the rate of decay for this function
     * @param string $offset If defined, this function will only be computed for documents with a distance from the origin greater than this value
     * @param float $decay optionally defines how documents are scored at the distance given by the $scale parameter
     * @param float $scaleWeight optional factor by which to multiply the score at the value provided by the $scale parameter
     * @param AbstractFilter $filter a filter associated with this function
     * @return \Elastica\Query\FunctionScore
     */
    public function addDecayFunction($function, $field, $origin, $scale, $offset = NULL, $decay = NULL, $scaleWeight = NULL,
                                     AbstractFilter $filter = NULL)
    {
        $functionParams = array(
            $field => array(
                'origin' => $origin,
                'scale' => $scale
            )
        );
        if (!is_null($offset)) {
            $functionParams[$field]['offset'] = $offset;
        }
        if (!is_null($decay)) {
            $functionParams[$field]['decay'] = (float)$decay;
        }
        if (!is_null($scaleWeight)) {
            $functionParams[$field]['scale_weight'] = (float)$scaleWeight;
        }
        return $this->addFunction($function, $functionParams, $filter);
    }

    /**
     * Add a boost_factor function to the query
     * @param float $boostFactor the boost factor value
     * @param AbstractFilter $filter a filter associated with this function
     */
    public function addBoostFactorFunction($boostFactor, AbstractFilter $filter = NULL)
    {
        $this->addFunction('boost_factor', $boostFactor, $filter);
    }

    /**
     * Add a random_score function to the query
     * @param number $seed the seed value
     * @param AbstractFilter $filter a filter associated with this function
     * @param float $boost an optional boost value associated with this function
     */
    public function addRandomScoreFunction($seed, AbstractFilter $filter = NULL, $boost = NULL)
    {
        $this->addFunction('random_score', array('seed' => $seed), $filter, $boost);
    }

    /**
     * Set an overall boost value for this query
     * @param float $boost
     * @return \Elastica\Query\FunctionScore
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', (float)$boost);
    }

    /**
     * Restrict the combined boost of the function_score query and its child query
     * @param float $maxBoost
     * @return \Elastica\Query\FunctionScore
     */
    public function setMaxBoost($maxBoost)
    {
        return $this->setParam('max_boost', (float)$maxBoost);
    }

    /**
     * The boost mode determines how the score of this query is combined with that of the child query
     * @param string $mode see BOOST_MODE_* constants for valid options. Default is multiply.
     * @return \Elastica\Query\FunctionScore
     */
    public function setBoostMode($mode)
    {
        return $this->setParam('boost_mode', $mode);
    }

    /**
     * If set, this query will return results in random order.
     * @param int $seed Set a seed value to return results in the same random order for consistent pagination.
     * @return \Elastica\Query\FunctionScore
     */
    public function setRandomScore($seed = NULL)
    {
        $seedParam = array();
        if (!is_null($seed)) {
            $seedParam['seed'] = $seed;
        }
        return $this->setParam('random_score', $seedParam);
    }

    /**
     * Set the score method
     * @param string $mode see SCORE_MODE_* constants for valid options. Default is multiply.
     * @return \Elastica\Query\FunctionScore
     */
    public function setScoreMode($mode)
    {
        return $this->setParam('score_mode', $mode);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (sizeof($this->_functions)) {
            $this->setParam('functions', $this->_functions);
        }
        return parent::toArray();
    }
}