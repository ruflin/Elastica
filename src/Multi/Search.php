<?php

namespace Elastica\Multi;

use Elastica\Client;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Search as BaseSearch;

/**
 * Elastica multi search.
 *
 * @author munkie
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-multi-search.html
 */
class Search
{
    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var BaseSearch[]
     */
    protected $_searches = [];
    /**
     * @const string[] valid header options
     */
    private static $HEADER_OPTIONS = [
        'index',
        'types',
        'search_type',
        'routing',
        'preference',
    ];

    /**
     * @var MultiBuilderInterface
     */
    private $_builder;

    public function __construct(Client $client, ?MultiBuilderInterface $builder = null)
    {
        $this->_builder = $builder ?? new MultiBuilder();
        $this->_client = $client;
    }

    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * @return $this
     */
    public function clearSearches(): self
    {
        $this->_searches = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function addSearch(BaseSearch $search, ?string $key = null): self
    {
        if ($key) {
            $this->_searches[$key] = $search;
        } else {
            $this->_searches[] = $search;
        }

        return $this;
    }

    /**
     * @param BaseSearch[] $searches
     *
     * @return $this
     */
    public function addSearches(array $searches): self
    {
        foreach ($searches as $key => $search) {
            $this->addSearch($search, $key);
        }

        return $this;
    }

    /**
     * @param BaseSearch[] $searches
     *
     * @return $this
     */
    public function setSearches(array $searches): self
    {
        $this->clearSearches();
        $this->addSearches($searches);

        return $this;
    }

    /**
     * @return BaseSearch[]
     */
    public function getSearches(): array
    {
        return $this->_searches;
    }

    /**
     * @return $this
     */
    public function setSearchType(string $searchType): self
    {
        $this->_options[BaseSearch::OPTION_SEARCH_TYPE] = $searchType;

        return $this;
    }

    public function search(): ResultSet
    {
        $data = $this->_getData();

        $response = $this->getClient()->request(
            '_msearch',
            Request::POST,
            $data,
            $this->_options,
            Request::NDJSON_CONTENT_TYPE
        );

        return $this->_builder->buildMultiResultSet($response, $this->getSearches());
    }

    protected function _getData(): string
    {
        $data = '';
        foreach ($this->getSearches() as $search) {
            $data .= $this->_getSearchData($search);
        }

        return $data;
    }

    protected function _getSearchData(BaseSearch $search): string
    {
        $header = $this->_getSearchDataHeader($search);

        $header = (empty($header)) ? new \stdClass() : $header;
        $query = $search->getQuery();

        // Keep other query options as part of the search body
        $queryOptions = \array_diff_key($search->getOptions(), \array_flip(self::$HEADER_OPTIONS));

        $data = JSON::stringify($header)."\n";
        $data .= JSON::stringify($query->toArray() + $queryOptions)."\n";

        return $data;
    }

    protected function _getSearchDataHeader(BaseSearch $search): array
    {
        $header = $search->getOptions();

        if ($search->hasIndices()) {
            $header['index'] = $search->getIndices();
        }

        // Filter options accepted in the "header"
        return \array_intersect_key($header, \array_flip(self::$HEADER_OPTIONS));
    }
}
