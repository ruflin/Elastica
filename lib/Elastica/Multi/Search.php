<?php
/**
 * Elastica multi search
 *
 * @category Xodoa
 * @package Elastica
 * @author munkie
 * @link http://www.elasticsearch.org/guide/reference/api/multi-search.html
 */
class Elastica_Multi_Search
{
    /**
     * @var array|Elastica_Search[]
     */
    protected $_searches = array();

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var Elastica_Client
     */
    protected $_client;

    /**
     * Constructs search object
     *
     * @param Elastica_Client $client Client object
     */
    public function __construct(Elastica_Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @return Elastica_Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param Elastica_Client $client
     * @return Elastica_Multi_Search
     */
    public function setClient(Elastica_Client $client)
    {
        $this->_client = $client;

        return $this;
    }

    /**
     * @return Elastica_Multi_Search
     */
    public function clearSearches()
    {
        $this->_searches = array();

        return $this;
    }

    /**
     * @param Elastica_Search $search
     * @return Elastica_Multi_Search
     */
    public function addSearch(Elastica_Search $search)
    {
        $this->_searches[] = $search;
        return $this;
    }

    /**
     * @param array|Elastica_Search[] $searches
     * @return Elastica_Multi_Search
     */
    public function addSearches(array $searches)
    {
        foreach ($searches as $search) {
            $this->addSearch($search);
        }
        return $this;
    }

    /**
     * @param array|Elastica_Search[] $searches
     * @return Elastica_Multi_Search
     */
    public function setSearches(array $searches)
    {
        $this->clearSearches();
        $this->addSearches($searches);

        return $this;
    }

    /**
     * @return array|Elastica_Search[]
     */
    public function getSearches()
    {
        return $this->_searches;
    }

    /**
     * @param string $searchType
     * @return Elastica_Multi_Search
     */
    public function setSearchType($searchType)
    {
        $this->_options[Elastica_Search::OPTION_SEARCH_TYPE] = $searchType;

        return $this;
    }

    /**
     * @return Elastica_Multi_ResultSet
     */
    public function search()
    {
        $data = $this->_getData();

        $response = $this->getClient()->request(
            '_msearch',
            Elastica_Request::POST,
            $data,
            $this->_options
        );

        return new Elastica_Multi_ResultSet($response, $this->getSearches());
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
     * @param Elastica_Search $search
     * @return string
     */
    protected function _getSearchData(Elastica_Search $search)
    {
        $header = $this->_getSearchDataHeader($search);
        $header = (empty($header)) ? new StdClass : $header;
        $query = $search->getQuery();

        $data = json_encode($header) . "\n";
        $data.= json_encode($query->toArray()) . "\n";

        return $data;
    }

    /**
     * @param Elastica_Search $search
     * @return array
     */
    protected function _getSearchDataHeader(Elastica_Search $search)
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
