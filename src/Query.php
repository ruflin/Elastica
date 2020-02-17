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
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html
 */
class Query extends Param
{
    /**
     * If the current query has a suggest in it.
     */
    private $hasSuggest = false;

    /**
     * Creates a query object.
     *
     * @param AbstractQuery|array $query Query object (default = null)
     */
    public function __construct($query = null)
    {
        if (\is_array($query)) {
            $this->setRawQuery($query);
        } elseif ($query instanceof AbstractQuery) {
            $this->setQuery($query);
        } elseif ($query instanceof Suggest) {
            $this->setSuggest($query);
        } elseif ($query instanceof Collapse) {
            $this->setCollapse($query);
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
     */
    public static function create($query): self
    {
        switch (true) {
            case empty($query):
                return new static(new MatchAll());
            case $query instanceof self:
                return $query;
            case $query instanceof AbstractSuggest:
                return new static(new Suggest($query));
            case $query instanceof AbstractQuery:
            case $query instanceof Suggest:
            case $query instanceof Collapse:
            case \is_array($query):
                return new static($query);
            case \is_string($query):
                return new static(new QueryString($query));
        }

        throw new InvalidException('Unexpected argument to create a query for.');
    }

    /**
     * Sets query as raw array. Will overwrite all already set arguments.
     *
     * @param array $query Query array
     */
    public function setRawQuery(array $query): self
    {
        $this->_params = $query;

        return $this;
    }

    public function setQuery(AbstractQuery $query): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Gets the query object.
     *
     * @return AbstractQuery|array
     */
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * Sets the start from which the search results should be returned.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-from-size
     */
    public function setFrom(int $from): self
    {
        return $this->setParam('from', $from);
    }

    /**
     * Sets sort arguments for the query
     * Replaces existing values.
     *
     * @param array $sortArgs Sorting arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html
     */
    public function setSort(array $sortArgs): self
    {
        return $this->setParam('sort', $sortArgs);
    }

    /**
     * Adds a sort param to the query.
     *
     * @param mixed $sort Sort parameter
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html
     */
    public function addSort($sort): self
    {
        return $this->addParam('sort', $sort);
    }

    /**
     * Keep track of the scores when sorting results.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html#_track_scores
     */
    public function setTrackScores(bool $trackScores = true): self
    {
        return $this->setParam('track_scores', $trackScores);
    }

    /**
     * Sets highlight arguments for the query.
     *
     * @param array $highlightArgs Set all highlight arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-highlighting.html
     */
    public function setHighlight(array $highlightArgs): self
    {
        return $this->setParam('highlight', $highlightArgs);
    }

    /**
     * Adds a highlight argument.
     *
     * @param mixed $highlight Add highlight argument
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-highlighting.html
     */
    public function addHighlight($highlight): self
    {
        return $this->addParam('highlight', $highlight);
    }

    /**
     * Sets maximum number of results for this query.
     *
     * @param int $size Maximal number of results for query (default = 10)
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-from-size
     */
    public function setSize(int $size = 10): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * Enables explain on the query.
     *
     * @param bool $explain Enabled or disable explain (default = true)
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-explain
     */
    public function setExplain($explain = true): self
    {
        return $this->setParam('explain', $explain);
    }

    /**
     * Enables version on the query.
     *
     * @param bool $version Enabled or disable version (default = true)
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-version
     */
    public function setVersion($version = true): self
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
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-stored-fields
     */
    public function setStoredFields(array $fields): self
    {
        return $this->setParam('stored_fields', $fields);
    }

    /**
     * Set the doc value representation of a fields to return for each hit.
     *
     * @param array $fieldDataFields Fields not stored to be returned
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-docvalue-fields
     */
    public function setFieldDataFields(array $fieldDataFields): self
    {
        return $this->setParam('docvalue_fields', $fieldDataFields);
    }

    /**
     * Set script fields.
     *
     * @param array|\Elastica\Script\ScriptFields $scriptFields Script fields
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-script-fields
     */
    public function setScriptFields($scriptFields): self
    {
        if (\is_array($scriptFields)) {
            $scriptFields = new ScriptFields($scriptFields);
        }

        return $this->setParam('script_fields', $scriptFields);
    }

    /**
     * Adds a Script to the query.
     */
    public function addScriptField(string $name, AbstractScript $script): self
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
     */
    public function addAggregation(AbstractAggregation $agg): self
    {
        $this->_params['aggs'][] = $agg;

        return $this;
    }

    /**
     * Converts all query params to an array.
     */
    public function toArray(): array
    {
        if (!$this->hasSuggest && !isset($this->_params['query'])) {
            $this->setQuery(new MatchAll());
        }

        if (isset($this->_params['post_filter']) && 0 === \count(($this->_params['post_filter'])->toArray())) {
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
     * @throws InvalidException
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-min-score
     */
    public function setMinScore(float $minScore): self
    {
        return $this->setParam('min_score', $minScore);
    }

    /**
     * Add a suggest term.
     *
     * @param Suggest $suggest suggestion object
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters.html
     */
    public function setSuggest(Suggest $suggest): self
    {
        $this->setParam('suggest', $suggest);
        $this->hasSuggest = true;

        return $this;
    }

    /**
     * Add a Rescore.
     *
     * @param mixed $rescore suggestion object
     *
     * @see: https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-rescore
     */
    public function setRescore($rescore): self
    {
        if (\is_array($rescore)) {
            $buffer = [];

            foreach ($rescore as $rescoreQuery) {
                $buffer[] = $rescoreQuery;
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
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-source-filtering
     */
    public function setSource($params): self
    {
        return $this->setParam('_source', $params);
    }

    /**
     * Sets a post_filter to the current query.
     *
     * @param AbstractQuery|array $filter
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-post-filter
     */
    public function setPostFilter(AbstractQuery $filter): self
    {
        return $this->setParam('post_filter', $filter);
    }

    /**
     * Allows to collapse search results based on field values.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-collapse
     */
    public function setCollapse(Collapse $collapse): self
    {
        return $this->setParam('collapse', $collapse);
    }

    /**
     * Adds a track_total_hits argument.
     *
     * @param bool|int $trackTotalHits Track total hits parameter
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-track-total-hits
     */
    public function setTrackTotalHits($trackTotalHits = true): self
    {
        if (!\is_bool($trackTotalHits) && !\is_int($trackTotalHits)) {
            throw new InvalidException('TrackTotalHits must be either a boolean, or an integer value');
        }

        return $this->setParam('track_total_hits', $trackTotalHits);
    }
}
