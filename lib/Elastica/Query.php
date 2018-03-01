<?php
namespace Elastica;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Exception\InvalidException;
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
     * Transforms the argument to a query object.
     *
     * For example, an empty argument will return a \Elastica\Query with a \Elastica\Query\MatchAll.
     *
     * @param mixed $query
     *
     * @throws InvalidException For an invalid argument
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

        throw new InvalidException('Unexpected argument to create a query for.');
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
     * Keep track of the scores when sorting results.
     *
     * @param bool $trackScores
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html#_track_scores
     */
    public function setTrackScores($trackScores = true)
    {
        return $this->setParam('track_scores', (bool) $trackScores);
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
    public function setStoredFields(array $fields)
    {
        return $this->setParam('stored_fields', $fields);
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
        return $this->setParam('docvalue_fields', $fieldDataFields);
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
        if (isset($this->_params['script_fields'])) {
            $this->_params['script_fields']->addScript($name, $script);
        } else {
            $this->setScriptFields([$name => $script]);
        }

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
            $buffer = [];

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
    public function setPostFilter(AbstractQuery $filter)
    {
        return $this->setParam('post_filter', $filter);
    }
}
