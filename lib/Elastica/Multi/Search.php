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
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-multi-search.html
 */
class Search
{
    /**
     * @const string[] valid header options
     */
    private static $HEADER_OPTIONS = ['index', 'types', 'search_type',
                                      'routing', 'preference', ];
    /**
     * @var MultiBuilderInterface
     */
    private $_builder;

    /**
     * @var \Elastica\Client
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var array|\Elastica\Search[]
     */
    protected $_searches = [];

    /**
     * Constructs search object.
     *
     * @param \Elastica\Client      $client  Client object
     * @param MultiBuilderInterface $builder
     */
    public function __construct(Client $client, MultiBuilderInterface $builder = null)
    {
        $this->_builder = $builder ?: new MultiBuilder();
        $this->_client = $client;
    }

    /**
     * @return \Elastica\Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @return $this
     */
    public function clearSearches()
    {
        $this->_searches = [];

        return $this;
    }

    /**
     * @param \Elastica\Search $search
     * @param string           $key    Optional key
     *
     * @return $this
     */
    public function addSearch(BaseSearch $search, $key = null)
    {
        if ($key) {
            $this->_searches[$key] = $search;
        } else {
            $this->_searches[] = $search;
        }

        return $this;
    }

    /**
     * @param array|\Elastica\Search[] $searches
     *
     * @return $this
     */
    public function addSearches(array $searches)
    {
        foreach ($searches as $key => $search) {
            $this->addSearch($search, $key);
        }

        return $this;
    }

    /**
     * @param array|\Elastica\Search[] $searches
     *
     * @return $this
     */
    public function setSearches(array $searches)
    {
        $this->clearSearches();
        $this->addSearches($searches);

        return $this;
    }

    /**
     * @return array|\Elastica\Search[]
     */
    public function getSearches()
    {
        return $this->_searches;
    }

    /**
     * @param string $searchType
     *
     * @return $this
     */
    public function setSearchType($searchType)
    {
        $this->_options[BaseSearch::OPTION_SEARCH_TYPE] = $searchType;

        return $this;
    }

    /**
     * @return \Elastica\Multi\ResultSet
     */
    public function search()
    {
        $data = $this->_getData();

        $response = $this->getClient()->request(
            '_msearch',
            Request::POST,
            $data,
            $this->_options
        );

        return $this->_builder->buildMultiResultSet($response, $this->getSearches());
    }

    /**
     * @return string
     */
    protected function _getData()
    {
        $data = '';
        foreach ($this->getSearches() as $search) {
            $data .= $this->_getSearchData($search);
        }

        return $data;
    }

    /**
     * @param \Elastica\Search $search
     *
     * @return string
     */
    protected function _getSearchData(BaseSearch $search)
    {
        $header = $this->_getSearchDataHeader($search);

        $header = (empty($header)) ? new \stdClass() : $header;
        $query = $search->getQuery();

        // Keep other query options as part of the search body
        $queryOptions = array_diff_key($search->getOptions(), array_flip(self::$HEADER_OPTIONS));

        $data = JSON::stringify($header)."\n";
        $data .= JSON::stringify($query->toArray() + $queryOptions)."\n";

        return $data;
    }

    /**
     * @param \Elastica\Search $search
     *
     * @return array
     */
    protected function _getSearchDataHeader(BaseSearch $search)
    {
        $header = $search->getOptions();

        if ($search->hasIndices()) {
            $header['index'] = $search->getIndices();
        }

        if ($search->hasTypes()) {
            $header['types'] = $search->getTypes();
        }

        // Filter options accepted in the "header"
        return array_intersect_key($header, array_flip(self::$HEADER_OPTIONS));
    }
}
