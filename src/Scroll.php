<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Scroll Iterator.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-scroll.html
 */
class Scroll implements \Iterator
{
    /**
     * @var string
     */
    public $expiryTime;

    /**
     * @var Search
     */
    protected $_search;

    /**
     * @var string|null
     */
    protected $_nextScrollId;

    /**
     * @var ResultSet|null
     */
    protected $_currentResultSet;

    /**
     * 0: scroll<br>
     * 1: scroll id.
     * 2: ignore_unavailable.
     *
     * @var array
     */
    protected $_options = [null, null, null];

    private $totalPages = 0;
    private $currentPage = 0;

    public function __construct(Search $search, string $expiryTime = '1m')
    {
        $this->_search = $search;
        $this->expiryTime = $expiryTime;
    }

    /**
     * Returns current result set.
     *
     * @see http://php.net/manual/en/iterator.current.php
     */
    public function current(): ResultSet
    {
        if (!$this->_currentResultSet) {
            throw new InvalidException('Could not fetch the current ResultSet from an invalid iterator. Did you forget to call "valid()"?');
        }

        return $this->_currentResultSet;
    }

    /**
     * Next scroll search.
     *
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->_currentResultSet = null;
        if ($this->currentPage < $this->totalPages) {
            $this->_saveOptions();

            $this->_search->setOption(Search::OPTION_SCROLL, $this->expiryTime);
            $this->_search->setOption(Search::OPTION_SCROLL_ID, $this->_nextScrollId);

            $this->_setScrollId($this->_search->search());

            $this->_revertOptions();
        } else {
            // If there are no pages left, we do not need to query ES.
            $this->clear();
        }
    }

    /**
     * Returns scroll id.
     *
     * @see http://php.net/manual/en/iterator.key.php
     */
    public function key(): ?string
    {
        return $this->_nextScrollId;
    }

    /**
     * Returns true if current result set contains at least one hit.
     *
     * @see http://php.net/manual/en/iterator.valid.php
     */
    public function valid(): bool
    {
        return null !== $this->_nextScrollId;
    }

    /**
     * Initial scroll search.
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        // reset state
        $this->_options = [null, null, null];
        $this->currentPage = 0;

        // initial search
        $this->_saveOptions();

        $this->_search->setOption(Search::OPTION_SCROLL, $this->expiryTime);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, null);
        $this->_currentResultSet = null;
        $this->_setScrollId($this->_search->search());

        $this->_revertOptions();
    }

    /**
     * Cleares the search context on ES and marks this Scroll instance as finished.
     */
    public function clear(): void
    {
        if (null !== $this->_nextScrollId) {
            $this->_search->getClient()->request(
                '_search/scroll',
                Request::DELETE,
                [Search::OPTION_SCROLL_ID => [$this->_nextScrollId]]
            );

            // Reset scroll ID so valid() returns false.
            $this->_nextScrollId = null;
        }
    }

    /**
     * Prepares Scroll for next request.
     */
    protected function _setScrollId(ResultSet $resultSet): void
    {
        if (0 === $this->currentPage) {
            $this->totalPages = $resultSet->count() > 0 ? \ceil($resultSet->getTotalHits() / $resultSet->count()) : 0;
        }

        $this->_currentResultSet = $resultSet;
        ++$this->currentPage;
        $this->_nextScrollId = null;
        if ($resultSet->getResponse()->isOk()) {
            $this->_nextScrollId = $resultSet->getResponse()->getScrollId();
            if (0 === $resultSet->count()) {
                $this->clear();
            }
        }
    }

    /**
     * Save all search options manipulated by Scroll.
     */
    protected function _saveOptions(): void
    {
        if ($this->_search->hasOption(Search::OPTION_SCROLL)) {
            $this->_options[0] = $this->_search->getOption(Search::OPTION_SCROLL);
        }

        if ($this->_search->hasOption(Search::OPTION_SCROLL_ID)) {
            $this->_options[1] = $this->_search->getOption(Search::OPTION_SCROLL_ID);
        }

        if ($this->_search->hasOption(Search::OPTION_SEARCH_IGNORE_UNAVAILABLE)) {
            $isNotInitial = (null !== $this->_options[2]);
            $this->_options[2] = $this->_search->getOption(Search::OPTION_SEARCH_IGNORE_UNAVAILABLE);

            // remove ignore_unavailable from options if not initial search
            if ($isNotInitial) {
                $searchOptions = $this->_search->getOptions();
                unset($searchOptions[Search::OPTION_SEARCH_IGNORE_UNAVAILABLE]);
                $this->_search->setOptions($searchOptions);
            }
        }
    }

    /**
     * Revert search options to previously saved state.
     */
    protected function _revertOptions(): void
    {
        $this->_search->setOption(Search::OPTION_SCROLL, $this->_options[0]);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, $this->_options[1]);
        if (null !== $this->_options[2]) {
            $this->_search->setOption(Search::OPTION_SEARCH_IGNORE_UNAVAILABLE, $this->_options[2]);
        }
    }
}
