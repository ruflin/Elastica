<?php

namespace Elastica\Multi;

use Elastica\Client;
use Elastica\Exception\InvalidException;
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

    /**
     * Constructs search object.
     */
    public function __construct(Client $client, ?MultiBuilderInterface $builder = null)
    {
        $this->_builder = $builder ?? new MultiBuilder();
        $this->_client = $client;
    }
    
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->_validateOption($key);

        $this->_options[$key] = $value;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->clearOptions();

        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearOptions()
    {
        $this->_options = [];

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addOption($key, $value)
    {
        $this->_validateOption($key);

        $this->_options[$key][] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->_options[$key]);
    }

    /**
     * @param string $key
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return mixed
     */
    public function getOption($key)
    {
        if (!$this->hasOption($key)) {
            throw new InvalidException('Option '.$key.' does not exist');
        }

        return $this->_options[$key];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param string $key
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return bool
     */
    protected function _validateOption($key)
    {
        switch ($key) {
            case BaseSearch::OPTION_FILTER_PATH:
                return true;
        }

        throw new InvalidException('Invalid option '.$key);
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
