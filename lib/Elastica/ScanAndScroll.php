<?php

namespace Elastica;

/**
 * Scan and Scroll Iterator.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/guide/current/scan-scroll.html
 */
class ScanAndScroll extends Scroll
{
    /**
     * @var int
     */
    public $sizePerShard;

    /**
     * Constructor.
     *
     * @param Search $search
     * @param string $expiryTime
     * @param int    $sizePerShard
     */
    public function __construct(Search $search, $expiryTime = '1m', $sizePerShard = 1000)
    {
        $this->sizePerShard = $sizePerShard;

        parent::__construct($search, $expiryTime);
    }

    /**
     * Initial scan search.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        // reset state
        $this->_nextScrollId = null;
        $this->_options = array(null, null, null, null);

        $this->_saveOptions();

        // initial scan request
        $this->_search->getQuery()->setSize($this->sizePerShard);
        $this->_search->setOption(Search::OPTION_SCROLL, $this->expiryTime);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, null);
        $this->_search->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_SCAN);
        $this->_setScrollId($this->_search->search());

        $this->_revertOptions();

        // first scroll request
        $this->next();
    }

    /**
     * Save all search options manipulated by Scroll.
     */
    protected function _saveOptions()
    {
        $query = $this->_search->getQuery();
        if ($query->hasParam('size')) {
            $this->_options[3] = $query->getParam('size');
        }

        parent::_saveOptions();
    }

    /**
     * Revert search options to previously saved state.
     */
    protected function _revertOptions()
    {
        $this->_search->getQuery()->setSize($this->_options[3]);

        parent::_revertOptions();
    }
}
