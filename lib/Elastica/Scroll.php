<?php
namespace Elastica;

/**
 * Scroll Iterator.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-scroll.html
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
     * @var null|string
     */
    protected $_nextScrollId;

    /**
     * @var null|ResultSet
     */
    protected $_currentResultSet;

    /**
     * 0: scroll<br>
     * 1: scroll id<br>
     * 2: search type.
     *
     * @var array
     */
    protected $_options = [null, null, null];

    /**
     * Constructor.
     *
     * @param Search $search
     * @param string $expiryTime
     */
    public function __construct(Search $search, $expiryTime = '1m')
    {
        $this->_search = $search;
        $this->expiryTime = $expiryTime;
    }

    /**
     * Returns current result set.
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return ResultSet
     */
    public function current()
    {
        return $this->_currentResultSet;
    }

    /**
     * Next scroll search.
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->_saveOptions();

        $this->_search->setOption(Search::OPTION_SCROLL, $this->expiryTime);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, $this->_nextScrollId);
        $this->_setScrollId($this->_search->search());

        $this->_revertOptions();
    }

    /**
     * Returns scroll id.
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return string
     */
    public function key()
    {
        return $this->_nextScrollId;
    }

    /**
     * Returns true if current result set contains at least one hit.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool
     */
    public function valid()
    {
        return
            $this->_nextScrollId !== null
            && $this->_currentResultSet !== null
            && $this->_currentResultSet->count() > 0;
    }

    /**
     * Initial scroll search.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        // reset state
        $this->_nextScrollId = null;
        $this->_options = [null, null, null];

        // initial search
        $this->_saveOptions();

        $this->_search->setOption(Search::OPTION_SCROLL, $this->expiryTime);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, null);
        $this->_setScrollId($this->_search->search());

        $this->_revertOptions();
    }

    /**
     * Prepares Scroll for next request.
     *
     * @param ResultSet $resultSet
     */
    protected function _setScrollId(ResultSet $resultSet)
    {
        $this->_currentResultSet = $resultSet;

        $this->_nextScrollId = null;
        if ($resultSet->getResponse()->isOk()) {
            $this->_nextScrollId = $resultSet->getResponse()->getScrollId();
        }
    }

    /**
     * Save all search options manipulated by Scroll.
     */
    protected function _saveOptions()
    {
        if ($this->_search->hasOption(Search::OPTION_SCROLL)) {
            $this->_options[0] = $this->_search->getOption(Search::OPTION_SCROLL);
        }

        if ($this->_search->hasOption(Search::OPTION_SCROLL_ID)) {
            $this->_options[1] = $this->_search->getOption(Search::OPTION_SCROLL_ID);
        }
    }

    /**
     * Revert search options to previously saved state.
     */
    protected function _revertOptions()
    {
        $this->_search->setOption(Search::OPTION_SCROLL, $this->_options[0]);
        $this->_search->setOption(Search::OPTION_SCROLL_ID, $this->_options[1]);
    }
}
