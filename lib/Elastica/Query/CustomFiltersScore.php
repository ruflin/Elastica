<?php

namespace Elastica\Query;

use Elastica\Filter\AbstractFilter;
use Elastica\Query as BaseQuery;
use Elastica\Script;

/**
 * Custom filtered score query. Needs a query and array of filters, with boosts
 *
 * @category Xodoa
 * @package Elastica
 * @author James Wilson <jwilson556@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/custom-filters-score-query.html
 */
class CustomFiltersScore extends AbstractQuery
{
    const SCORE_MODE_FIRST      = 'first';
    const SCORE_MODE_MIN        = 'min';
    const SCORE_MODE_MAX        = 'max';
    const SCORE_MODE_TOTAL      = 'total';
    const SCORE_MODE_AVG        = 'avg';
    const SCORE_MODE_MULTIPLY   = 'multiply';

    /**
     * @param mixed $query Query object or data to build it, match_all query will be created be default
     */
    public function __construct($query = null)
    {
        $this->setQuery($query);
    }

    /**
     * Sets a query
     *
     * @param  mixed                                  $query Query object or data to build it
     * @return \Elastica\Query\CustomFiltersScore Current object
     */
    public function setQuery($query)
    {
        $query = BaseQuery::create($query);
        $data = $query->toArray();
        $this->setParam('query', $data['query']);

        return $this;
    }

    /**
     * Add a filter with boost
     *
     * @param  \Elastica\Filter\AbstractFilter         $filter Filter object
     * @param  float                                  $boost  Boost for the filter
     * @return \Elastica\Query\CustomFiltersScore Current object
     */
    public function addFilter(AbstractFilter $filter, $boost)
    {
        $filterParam = array(
            'filter' => $filter->toArray(),
            'boost' => $boost
        );

        return $this->addParam('filters', $filterParam);
    }

    /**
     * Add a filter with a script to calculate the score
     * Only script part of script object is used
     *
     * @param  \Elastica\Filter\AbstractFilter         $filter Filter object
     * @param  \Elastica\Script|string|array           $script Script for calculating the score
     * @return \Elastica\Query\CustomFiltersScore Current object
     */
    public function addFilterScript(AbstractFilter $filter, $script)
    {
        $script = Script::create($script);
        $filterParam = array(
            'filter' => $filter->toArray(),
            'script' => $script->getScript()
        );

        return $this->addParam('filters', $filterParam);
    }

    /**
     * Set lang fot scripts in filters
     *
     * @param  string                                 $lang lang
     * @return \Elastica\Query\CustomFiltersScore current object
     */
    public function setScriptLang($lang)
    {
        return $this->setParam('lang', $lang);
    }

    /**
     * Set params for scripts in filters
     * @param  array                                  $params
     * @return \Elastica\Query\CustomFiltersScore current object
     */
    public function setScriptParams(array $params)
    {
        return $this->setParam('params', $params);
    }

    /**
     * @param  string                                 $scoreMode
     * @return \Elastica\Query\CustomFiltersScore current object
     */
    public function setScoreMode($scoreMode)
    {
        return $this->setParam('score_mode', $scoreMode);
    }
}
