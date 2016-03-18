<?php

namespace Elastica;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotImplementedException;
use Elastica\Filter\AbstractFilter;
use Elastica\Query\AbstractQuery;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Script\AbstractScript;
use Elastica\Script\ScriptFields;
use Elastica\Suggest\AbstractSuggest;

/**
 * Elastica query object.
 *
 * Creates different types of queries
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html
 */
class Query extends Param
{
    /**
     * Params.
     *
     * @var array Params
     */
    protected $_params = array();

    /**
     * Suggest query or not.
     *
     * @var int Suggest
     */
    protected $_suggest = 0;

    /**
     * Creates a query object.
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
     * Transforms a string or an array to a query object.
     *
     * If query is empty,
     *
     * @param mixed $query
     *
     * @throws \Elastica\Exception\NotImplementedException
     *
     * @return self
     */
    public static function create($query)
    {
        switch (true) {
            case $query instanceof self:
                return $query;
            case $query instanceof AbstractQuery:
                return new self($query);
            case $query instanceof AbstractFilter:
                trigger_error('Deprecated: Elastica\Query::create() passing filter is deprecated. Create query and use setPostFilter with AbstractQuery instead.', E_USER_DEPRECATED);
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
     * Sets query as raw array. Will overwrite all already set arguments.
     *
     * @param array $query Query array
     *
     * @return $this
     */
    public function setRawQuery(array $query)
    {
        $this->_params = $query;

        return $this;
    }

    /**
     * Sets the query.
     *
     * @param \Elastica\Query\AbstractQuery $query Query object
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query);
    }

    /**
     * Gets the query object.
     *
     * @return \Elastica\Query\AbstractQuery
     **/
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * Set Filter.
     *
     * @param \Elastica\Query\AbstractQuery $filter Filter object
     *
     * @return $this
     *
     * @link    https://github.com/elasticsearch/elasticsearch/issues/7422
     * @deprecated Use Elastica\Query::setPostFilter() instead, this method will be removed in further Elastica releases
     */
    public function setFilter($filter)
    {
        if ($filter instanceof AbstractFilter) {
            trigger_error('Deprecated: Elastica\Query::setFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.', E_USER_DEPRECATED);
        } elseif (!($filter instanceof AbstractQuery)) {
            throw new InvalidException('Filter must be instance of AbstractQuery');
        }

        trigger_error('Deprecated: Elastica\Query::setFilter() is deprecated and will be removed in further Elastica releases. Use Elastica\Query::setPostFilter() instead.', E_USER_DEPRECATED);

        return $this->setPostFilter($filter);
    }

    /**
     * Sets the start from which the search results should be returned.
     *
     * @param int $from
     *
     * @return $this
     */
    public function setFrom($from)
    {
        return $this->setParam('from', $from);
    }

    /**
     * Sets sort arguments for the query
     * Replaces existing values.
     *
     * @param array $sortArgs Sorting arguments
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html
     */
    public function setSort(array $sortArgs)
    {
        return $this->setParam('sort', $sortArgs);
    }

    /**
     * Adds a sort param to the query.
     *
     * @param mixed $sort Sort parameter
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html
     */
    public function addSort($sort)
    {
        return $this->addParam('sort', $sort);
    }

    /**
     * Sets highlight arguments for the query.
     *
     * @param array $highlightArgs Set all highlight arguments
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-highlighting.html
     */
    public function setHighlight(array $highlightArgs)
    {
        return $this->setParam('highlight', $highlightArgs);
    }

    /**
     * Adds a highlight argument.
     *
     * @param mixed $highlight Add highlight argument
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-highlighting.html
     */
    public function addHighlight($highlight)
    {
        return $this->addParam('highlight', $highlight);
    }

    /**
     * Sets maximum number of results for this query.
     *
     * @param int $size OPTIONAL Maximal number of results for query (default = 10)
     *
     * @return $this
     */
    public function setSize($size = 10)
    {
        return $this->setParam('size', $size);
    }

    /**
     * Alias for setSize.
     *
     * @deprecated Use the setSize() method, this method will be removed in further Elastica releases
     *
     * @param int $limit OPTIONAL Maximal number of results for query (default = 10)
     *
     * @return $this
     */
    public function setLimit($limit = 10)
    {
        trigger_error('Deprecated: Elastica\Query::setLimit() is deprecated. Use setSize method instead. This method will be removed in further Elastica releases.', E_USER_DEPRECATED);

        return $this->setSize($limit);
    }

    /**
     * Enables explain on the query.
     *
     * @param bool $explain OPTIONAL Enabled or disable explain (default = true)
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-explain.html
     */
    public function setExplain($explain = true)
    {
        return $this->setParam('explain', $explain);
    }

    /**
     * Enables version on the query.
     *
     * @param bool $version OPTIONAL Enabled or disable version (default = true)
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-version.html
     */
    public function setVersion($version = true)
    {
        return $this->setParam('version', $version);
    }

    /**
     * Sets the fields to be returned by the search
     * NOTICE php will encode modified(or named keys) array into object format in json format request
     * so the fields array must a sequence(list) type of array.
     *
     * @param array $fields Fields to be returned
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-fields.html
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets the fields not stored to be returned by the search.
     *
     * @param array $fieldDataFields Fields not stored to be returned
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-fielddata-fields.html
     */
    public function setFieldDataFields(array $fieldDataFields)
    {
        return $this->setParam('fielddata_fields', $fieldDataFields);
    }

    /**
     * Set script fields.
     *
     * @param array|\Elastica\Script\ScriptFields $scriptFields Script fields
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-script-fields.html
     */
    public function setScriptFields($scriptFields)
    {
        if (is_array($scriptFields)) {
            $scriptFields = new ScriptFields($scriptFields);
        }

        return $this->setParam('script_fields', $scriptFields);
    }

    /**
     * Adds a Script to the query.
     *
     * @param string                          $name
     * @param \Elastica\Script\AbstractScript $script Script object
     *
     * @return $this
     */
    public function addScriptField($name, AbstractScript $script)
    {
        $this->_params['script_fields'][$name] = $script;

        return $this;
    }

    /**
     * Adds an Aggregation to the query.
     *
     * @param AbstractAggregation $agg
     *
     * @return $this
     */
    public function addAggregation(AbstractAggregation $agg)
    {
        if (!array_key_exists('aggs', $this->_params)) {
            $this->_params['aggs'] = array();
        }

        $this->_params['aggs'][] = $agg;

        return $this;
    }

    /**
     * Converts all query params to an array.
     *
     * @return array Query array
     */
    public function toArray()
    {
        if (!isset($this->_params['query']) && ($this->_suggest == 0)) {
            $this->setQuery(new MatchAll());
        }

        if (isset($this->_params['post_filter']) && 0 === count($this->_params['post_filter'])) {
            unset($this->_params['post_filter']);
        }

        $array = $this->_convertArrayable($this->_params);

        if (isset($array['suggest'])) {
            $array['suggest'] = $array['suggest']['suggest'];
        }

        return $array;
    }

    /**
     * Allows filtering of documents based on a minimum score.
     *
     * @param float $minScore Minimum score to filter documents by
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function setMinScore($minScore)
    {
        if (!is_numeric($minScore)) {
            throw new InvalidException('has to be numeric param');
        }

        return $this->setParam('min_score', $minScore);
    }

    /**
     * Add a suggest term.
     *
     * @param \Elastica\Suggest $suggest suggestion object
     *
     * @return $this
     */
    public function setSuggest(Suggest $suggest)
    {
        $this->setParam('suggest', $suggest);

        $this->_suggest = 1;

        return $this;
    }

    /**
     * Add a Rescore.
     *
     * @param mixed $rescore suggestion object
     *
     * @return $this
     */
    public function setRescore($rescore)
    {
        if (is_array($rescore)) {
            $buffer = array();

            foreach ($rescore as $rescoreQuery) {
                $buffer [] = $rescoreQuery;
            }
        } else {
            $buffer = $rescore;
        }

        return $this->setParam('rescore', $buffer);
    }

    /**
     * Sets the _source field to be returned with every hit.
     *
     * @param array|bool $params Fields to be returned or false to disable source
     *
     * @return $this
     *
     * @link   https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-source-filtering.html
     */
    public function setSource($params)
    {
        return $this->setParam('_source', $params);
    }

    /**
     * Sets post_filter argument for the query. The filter is applied after the query has executed.
     *
     * @param array|\Elastica\Query\AbstractQuery $filter
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-post-filter.html
     */
    public function setPostFilter($filter)
    {
        if (is_array($filter)) {
            trigger_error('Deprecated: Elastica\Query::setPostFilter() passing filter as array is deprecated. Pass instance of AbstractQuery instead.', E_USER_DEPRECATED);
        } elseif ($filter instanceof AbstractFilter) {
            trigger_error('Deprecated: Elastica\Query::setPostFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.', E_USER_DEPRECATED);
        } elseif (!($filter instanceof AbstractQuery)) {
            throw new InvalidException('Filter must be instance of AbstractQuery');
        }

        return $this->setParam('post_filter', $filter);
    }
}
