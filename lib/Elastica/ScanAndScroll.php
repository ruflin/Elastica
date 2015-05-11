<?php

namespace Elastica;

/**
 * scan and scroll object
 *
 * @category Xodoa
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@gmail.com>
 * @link http://www.elastic.co/guide/en/elasticsearch/guide/current/scan-scroll.html
 */
class ScanAndScroll implements \Iterator
{
    /**
     * time value parameter
     *
     * @var string
     */
    public $expiryTime;

    /**
     * @var int
     */
    public $sizePerShard;

    /**
     * @var Search
     */
    protected $_search;

    /**
     * @var null|string
     */
    protected $_nextScrollId = null;

    /**
     * @var null|string
     */
    protected $_lastScrollId = null;

    /**
     * @var null|ResultSet
     */
    protected $_currentResultSet = null;

    /**
     * By default, ES's scroll-scan respects sorting on the result set although with a performance cost.
     * Elastica ignores by default sorting on scroll for performance reasons, but this flag allows to do
     * not ignore it when needed
     *
     * @see ScanAndScroll::ignoreSorting()
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/1.5/search-request-scroll.html#scroll-scan
     *
     * @var bool
     */
    private $ignoreSorting;

    /**
     * Constructs scroll iterator object
     *
     * @param Search $search
     * @param string $expiryTime
     * @param int    $sizePerShard
     * @param bool   $ignoreSorting
     */
    public function __construct(Search $search, $expiryTime = '1m', $sizePerShard = 1000, $ignoreSorting = true)
    {
        $this->_search = $search;
        $this->expiryTime = $expiryTime;
        $this->sizePerShard = $sizePerShard;
        $this->ignoreSorting($ignoreSorting);
    }

    /**
     * Return the current result set
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return ResultSet
     */
    public function current()
    {
        return $this->_currentResultSet;
    }

    /**
     * Perform next scroll search
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void
     */
    public function next()
    {
        $this->_scroll();
    }

    /**
     * Return the scroll id of current scroll request
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return string
     */
    public function key()
    {
        return $this->_lastScrollId;
    }

    /**
     * Returns true if current result set contains one hit
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean
     */
    public function valid()
    {
        return
            $this->_nextScrollId !== null
            && $this->_currentResultSet !== null
            && $this->_currentResultSet->count() > 0;
    }

    /**
     * Start the initial scan search
     * @link http://php.net/manual/en/iterator.rewind.php
     * @throws \Elastica\Exception\InvalidException
     *
     * @return void
     */
    public function rewind()
    {
        $this->_search->getQuery()->setSize($this->sizePerShard);

        if ($this->ignoreSorting) {
            $this->_search->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_SCAN);
        }
        $this->_search->setOption(Search::OPTION_SCROLL, $this->expiryTime);

        // initial scan request
        $this->_setScrollId($this->_search->search());

        // trigger first scroll request
        $this->_scroll();
    }

    /**
     * Sets whether sorting should be ignored when scrolling
     *
     * @param bool $boolean
     */
    public function ignoreSorting($boolean)
    {
        if (!is_bool($boolean)) {
            throw new \InvalidArgumentException();
        }

        $this->ignoreSorting = $boolean;
    }

    /**
     * Perform next scroll search
     * @throws \Elastica\Exception\InvalidException
     */
    protected function _scroll()
    {
        $this->_search->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_SCROLL);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, $this->_nextScrollId);

        $resultSet = $this->_search->search();
        $this->_currentResultSet = $resultSet;
        $this->_setScrollId($resultSet);
    }

    /**
     * Save last scroll id and extract the new one if possible
     * @param ResultSet $resultSet
     */
    protected function _setScrollId(ResultSet $resultSet)
    {
        $this->_lastScrollId = $this->_nextScrollId;

        $this->_nextScrollId = null;
        if ($resultSet->getResponse()->isOk()) {
            $this->_nextScrollId = $resultSet->getResponse()->getScrollId();
        }
    }
}
