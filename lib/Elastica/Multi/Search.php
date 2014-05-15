<?php

namespace Elastica\Multi;

use Elastica\Client;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Search as BaseSearch;

/**
 * Elastica multi search
 *
 * @category Xodoa
 * @package Elastica
 * @author munkie
 * @link http://www.elasticsearch.org/guide/reference/api/multi-search.html
 */
class Search
{
    /**
     * @var array|\Elastica\Search[]
     */
    protected $_searches = array();

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var \Elastica\Client
     */
    protected $_client;

    /**
     * Constructs search object
     *
     * @param \Elastica\Client $client Client object
     */
    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @return \Elastica\Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param  \Elastica\Client       $client
     * @return \Elastica\Multi\Search
     */
    public function setClient(Client $client)
    {
        $this->_client = $client;

        return $this;
    }

    /**
     * @return \Elastica\Multi\Search
     */
    public function clearSearches()
    {
        $this->_searches = array();

        return $this;
    }

  /**
   * @param  \Elastica\Search $search
   * @param  string           $key      Optional key
   * @return \Elastica\Multi\Search
   */
    public function addSearch(BaseSearch $search, $key = null)
    {
        if ($key) {
          $this->_searches[$key] = $search;
        } else {
          $this->_searches[]     = $search;
        }

        return $this;
    }

    /**
     * @param  array|\Elastica\Search[] $searches
     * @return \Elastica\Multi\Search
     */
    public function addSearches(array $searches)
    {
        foreach ($searches as $key => $search) {
            $this->addSearch($search, $key);
        }

        return $this;
    }

    /**
     * @param  array|\Elastica\Search[] $searches
     * @return \Elastica\Multi\Search
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
     * @param  string                $searchType
     * @return \Elastica\Multi\Search
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

        return new ResultSet($response, $this->getSearches());
    }

    /**
     * @return string
     */
    protected function _getData()
    {
        $data = '';
        foreach ($this->getSearches() as $search) {
            $data.= $this->_getSearchData($search);
        }

        return $data;
    }

    /**
     * @param  \Elastica\Search $search
     * @return string
     */
    protected function _getSearchData(BaseSearch $search)
    {
        $header = $this->_getSearchDataHeader($search);
        $header = (empty($header)) ? new \stdClass : $header;
        $query = $search->getQuery();

        $data = JSON::stringify($header) . "\n";
        $data.= JSON::stringify($query->toArray()) . "\n";

        return $data;
    }

    /**
     * @param  \Elastica\Search $search
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

        return $header;
    }
}
