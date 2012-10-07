<?php

/**
 * Encapsulates the search API.
 *
 * @link http://www.elasticsearch.org/guide/reference/api/search/
 */
class Elastica_Search_Abstract
{
    const SEARCH_TYPE_QUERY_AND_FETCH = 'query_and_fetch';

    const SEARCH_TYPE_QUERY_THEN_FETCH = 'query_then_fetch';

    const SEARCH_TYPE_DFS_QUERY_AND_FETCH = 'dfs_query_and_fetch';

    const SEARCH_TYPE_DFS_QUERY_THEN_FETCH = 'dfs_query_then_fetch';

    const SEARCH_TYPE_COUNT = 'count';

    const SEARCH_TYPE_SCAN = 'scan';

    /**
     * The type of search.
     *
     * @link http://www.elasticsearch.org/guide/reference/api/search/search-type.html
     * @var string
     */
    protected $_searchType;

    /**
     * The keep alive time of a scan request.
     *
     * @var string
     */
    protected $_scroll;

    /**
     * The value to route the request on.
     *
     * @var string
     */
    protected $_routing;

    /**
     * Stats groups the search is associated with.
     *
     * @var array
     */
    protected $_statsGroups = array();

    /**
     * Sets the search type.
     *
     * @param string $searchType
     * @return Elastica_Search_Abstract
     */
    public function setSearchType($searchType)
    {
        $this->_searchType = $searchType;
        return $this;
    }

    /**
     * Gets the search type.
     *
     * @return string
     */
    public function getSearchType()
    {
        return $this->_searchType;
    }

    /**
     * Sets the keep alive time of a scan request.
     *
     * @param string $scroll
     * @return Elastica_Search_Abstract
     */
    public function setScroll($scroll)
    {
        $this->_scroll = $scroll;
        return $this;
    }

    /**
     * Gets the keep alive time of a scan request.
     *
     * @return string
     */
    public function getScroll()
    {
        return $this->_scroll;
    }

    /**
     * Sets the routing value for this search.
     *
     * @param string $routing
     * @return Elastica_Search_Abstract
     */
    public function setRouting($routing)
    {
        $this->_routing = $routing;
        return $this;
    }

    /**
     * Gets the routing value for this search.
     *
     * @return string
     */
    public function getRouting()
    {
        return $this->_routing;
    }

    /**
     * Adds a stat group to associate with this search.
     *
     * @param string $statsGroup
     * @return Elastica_Search_Abstract
     */
    public function addStatsGroup($statsGroup)
    {
        $this->_statsGroups[] = $statsGroup;
        return $this;
    }

    /**
     * Gets the stats groups associated with this search.
     *
     * @return array
     */
    public function getStatsGroups()
    {
        return $this->_statsGroups;
    }
}
