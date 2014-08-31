<?php

namespace Elastica;
use Elastica\Aggregation\AbstractAggregation;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotImplementedException;
use Elastica\Facet\AbstractFacet;
use Elastica\Filter\AbstractFilter;
use Elastica\Query\AbstractQuery;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Suggest\AbstractSuggest;
use Elastica\Suggest;

/**
 * Elastica query object
 *
 * Creates different types of queries
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/
 */
class Query extends Param
{
    /**
     * Params
     *
     * @var array Params
     */
    protected $_params = array();
    
    /**
    * Suggest query or not
    *
    * @var int Suggest
    */
    protected $_suggest = 0;

    /**
     * Creates a query object
     *
     * @param array|\Elastica\Query\AbstractQuery $query OPTIONAL Query object (default = null)
     */
    public function __construct($query = null)
    {
        if (is_array($query)) {
            $this->setRawQuery($query);
        } elseif ($query instanceof AbstractQuery) {
            $this->setQuery($query);
        } elseif ($query instanceof Suggest) {
            $this->setSuggest($query);
        }
    }

    /**
     * Transforms a string or an array to a query object
     *
     * If query is empty,
     *
     * @param  mixed                                      $query
     * @throws \Elastica\Exception\NotImplementedException
     * @return \Elastica\Query
     */
    public static function create($query)
    {
        switch (true) {
            case $query instanceof Query:
                return $query;
            case $query instanceof AbstractQuery:
                return new self($query);
            case $query instanceof AbstractFilter:
                $newQuery = new self();
                $newQuery->setPostFilter($query);

                return $newQuery;
            case empty($query):
                return new self(new MatchAll());
            case is_array($query):
                return new self($query);
            case is_string($query):
                return new self(new QueryString($query));
            case $query instanceof AbstractSuggest:
                return new self(new Suggest($query));

            case $query instanceof Suggest:
                return new self($query);

        }

        // TODO: Implement queries without
        throw new NotImplementedException();
    }

    /**
     * Sets query as raw array. Will overwrite all already set arguments
     *
     * @param  array          $query Query array
     * @return \Elastica\Query Query object
     */
    public function setRawQuery(array $query)
    {
        $this->_params = $query;

        return $this;
    }

    /**
     * Sets the query
     *
     * @param  \Elastica\Query\AbstractQuery $query Query object
     * @return \Elastica\Query               Query object
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query->toArray());
    }

    /**
     * Gets the query array
     *
     * @return array
     **/
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * Set Filter
     *
     * @param  \Elastica\Filter\AbstractFilter $filter Filter object
     * @return \Elastica\Query                 Current object
     * @link    https://github.com/elasticsearch/elasticsearch/issues/7422
     * @deprecated
     */
    public function setFilter(AbstractFilter $filter)
    {
        trigger_error('Deprecated: Elastica\Query::setFilter() is deprecated. Use Elastica\Query::setPostFilter() instead.', E_USER_DEPRECATED);
        return $this->setPostFilter($filter);
    }

    /**
     * Sets the start from which the search results should be returned
     *
     * @param  int            $from
     * @return \Elastica\Query Query object
     */
    public function setFrom($from)
    {
        return $this->setParam('from', $from);
    }

    /**
     * Sets sort arguments for the query
     * Replaces existing values
     *
     * @param  array          $sortArgs Sorting arguments
     * @return \Elastica\Query Query object
     * @link http://www.elasticsearch.org/guide/reference/api/search/sort.html
     */
    public function setSort(array $sortArgs)
    {
        return $this->setParam('sort', $sortArgs);
    }

    /**
     * Adds a sort param to the query
     *
     * @param  mixed          $sort Sort parameter
     * @return \Elastica\Query Query object
     * @link http://www.elasticsearch.org/guide/reference/api/search/sort.html
     */
    public function addSort($sort)
    {
        return $this->addParam('sort', $sort);
    }

    /**
     * Sets highlight arguments for the query
     *
     * @param  array          $highlightArgs Set all highlight arguments
     * @return \Elastica\Query Query object
     * @link http://www.elasticsearch.org/guide/reference/api/search/highlighting.html
     */
    public function setHighlight(array $highlightArgs)
    {
        return $this->setParam('highlight', $highlightArgs);
    }

    /**
     * Adds a highlight argument
     *
     * @param  mixed          $highlight Add highlight argument
     * @return \Elastica\Query Query object
     * @link http://www.elasticsearch.org/guide/reference/api/search/highlighting.html
     */
    public function addHighlight($highlight)
    {
        return $this->addParam('highlight', $highlight);
    }

    /**
     * Sets maximum number of results for this query
     *
     * @param  int            $size OPTIONAL Maximal number of results for query (default = 10)
     * @return \Elastica\Query Query object
     */
    public function setSize($size = 10)
    {
        return $this->setParam('size', $size);
        
    }

    /**
     * Alias for setSize
     *
     * @deprecated Use the setSize() method, this method will be removed in future releases
     * @param  int            $limit OPTIONAL Maximal number of results for query (default = 10)
     * @return \Elastica\Query Query object
     */
    public function setLimit($limit = 10)
    {
        return $this->setSize($limit);
    }

    /**
     * Enables explain on the query
     *
     * @param  bool           $explain OPTIONAL Enabled or disable explain (default = true)
     * @return \Elastica\Query Current object
     * @link http://www.elasticsearch.org/guide/reference/api/search/explain.html
     */
    public function setExplain($explain = true)
    {
        return $this->setParam('explain', $explain);
    }

    /**
     * Enables version on the query
     *
     * @param  bool           $version OPTIONAL Enabled or disable version (default = true)
     * @return \Elastica\Query Current object
     * @link http://www.elasticsearch.org/guide/reference/api/search/version.html
     */
    public function setVersion($version = true)
    {
        return $this->setParam('version', $version);
    }

    /**
     * Sets the fields to be returned by the search
     * NOTICE php will encode modified(or named keys) array into object format in json format request
     * so the fields array must a sequence(list) type of array
     *
     * @param  array          $fields Fields to be returned
     * @return \Elastica\Query Current object
     * @link http://www.elasticsearch.org/guide/reference/api/search/fields.html
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Set script fields
     *
     * @param  array|\Elastica\ScriptFields $scriptFields Script fields
     * @return \Elastica\Query              Current object
     * @link http://www.elasticsearch.org/guide/reference/api/search/script-fields.html
     */
    public function setScriptFields($scriptFields)
    {
        if (is_array($scriptFields)) {
            $scriptFields = new ScriptFields($scriptFields);
        }

        return $this->setParam('script_fields', $scriptFields->toArray());
    }

    /**
     * Adds a Script to the query
     *
     * @param  string          $name
     * @param  \Elastica\Script $script Script object
     * @return \Elastica\Query  Query object
     */
    public function addScriptField($name, Script $script)
    {
        $this->_params['script_fields'][$name] = $script->toArray();

        return $this;
    }

    /**
     * Sets all facets for this query object. Replaces existing facets
     *
     * @param  array          $facets List of facet objects
     * @return \Elastica\Query Query object
     * @link http://www.elasticsearch.org/guide/reference/api/search/facets/
     */
    public function setFacets(array $facets)
    {
        $this->_params['facets'] = array();
        foreach ($facets as $facet) {
            $this->addFacet($facet);
        }

        return $this;
    }

    /**
     * Adds a Facet to the query
     *
     * @param  \Elastica\Facet\AbstractFacet $facet Facet object
     * @return \Elastica\Query               Query object
     */
    public function addFacet(AbstractFacet $facet)
    {
        $this->_params['facets'][$facet->getName()] = $facet->toArray();

        return $this;
    }

    /**
     * Adds an Aggregation to the query
     *
     * @param AbstractAggregation $agg
     * @return \Elastica\Query Query object
     */
    public function addAggregation(AbstractAggregation $agg)
    {
        if (!array_key_exists('aggs', $this->_params)) {
            $this->_params['aggs'] = array();
        }
        $this->_params['aggs'][$agg->getName()] = $agg->toArray();
        return $this;
    }

    /**
     * Converts all query params to an array
     *
     * @return array Query array
     */
    public function toArray()
    {
        if (!isset($this->_params['query']) && ($this->_suggest == 0)) {
            $this->setQuery(new MatchAll());
        }

        if (isset($this->_params['facets']) && 0 === count($this->_params['facets'])) {
            unset($this->_params['facets']);
        }

        if (isset($this->_params['post_filter']) && 0 === count($this->_params['post_filter'])) {
            unset($this->_params['post_filter']);
        }

        return $this->_params;
    }

    /**
     * Allows filtering of documents based on a minimum score
     *
     * @param  int                                 $minScore Minimum score to filter documents by
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Query                      Query object
     */
    public function setMinScore($minScore)
    {
        if (!is_numeric($minScore)) {
            throw new InvalidException('has to be numeric param');
        }

        return $this->setParam('min_score', $minScore);
    }

    /**
     * Add a suggest term
     *
     * @param  \Elastica\Suggest $suggest suggestion object
     */
    public function setSuggest(Suggest $suggest)
    {
        $this->addParam(NULL, $suggest->toArray());
        $this->_suggest = 1;
    }

    /**
     * Add a Rescore
     *
     * @param  \Elastica\Rescore\AbstractRescore $suggest suggestion object
     */
    public function setRescore($rescore)
    {
        $this->setParam('rescore', $rescore->toArray());
    }

    /**
     * Sets the _source field to be returned with every hit
     *
     * @param  array          $fields Fields to be returned
     * @return \Elastica\Query Current object
     * @link   http://www.elasticsearch.org/guide/en/elasticsearch/reference/1.x/search-request-source-filtering.html
     */
    public function setSource(array $fields)
    {
        return $this->setParam('_source', $fields);
    }

    /**
     * Sets post_filter argument for the query. The filter is applied after the query has executed
     *
     * @param   array|\Elastica\Filter\AbstractFilter $filter
     * @return  \Elastica\Param
     * @link    http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-post-filter.html
     */
    public function setPostFilter($filter)
    {
        if($filter instanceof AbstractFilter)
        {
            $filter = $filter->toArray();
        } else {
            trigger_error('Deprecated: Elastica\Query::setPostFilter() passing filter as array is deprecated. Pass instance of AbstractFilter instead.', E_USER_DEPRECATED);
        }

        return $this->setParam("post_filter", $filter);
    }
}



