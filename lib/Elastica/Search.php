<?php
namespace Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;
use Elastica\ResultSet\BuilderInterface;
use Elastica\ResultSet\DefaultBuilder;

/**
 * Elastica search object.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Search
{
    /*
     * Options
     */
    const OPTION_SEARCH_TYPE = 'search_type';
    const OPTION_ROUTING = 'routing';
    const OPTION_PREFERENCE = 'preference';
    const OPTION_VERSION = 'version';
    const OPTION_TIMEOUT = 'timeout';
    const OPTION_FROM = 'from';
    const OPTION_SIZE = 'size';
    const OPTION_SCROLL = 'scroll';
    const OPTION_SCROLL_ID = 'scroll_id';
    const OPTION_QUERY_CACHE = 'query_cache';

    /*
     * Search types
     */
    const OPTION_SEARCH_TYPE_COUNT = 'count';
    const OPTION_SEARCH_TYPE_SCAN = 'scan';
    const OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH = 'dfs_query_then_fetch';
    const OPTION_SEARCH_TYPE_DFS_QUERY_AND_FETCH = 'dfs_query_and_fetch';
    const OPTION_SEARCH_TYPE_QUERY_THEN_FETCH = 'query_then_fetch';
    const OPTION_SEARCH_TYPE_QUERY_AND_FETCH = 'query_and_fetch';
    const OPTION_SEARCH_TYPE_SUGGEST = 'suggest';
    const OPTION_SEARCH_TYPE_SCROLL = 'scroll';
    const OPTION_SEARCH_IGNORE_UNAVAILABLE = 'ignore_unavailable';

    /**
     * @var BuilderInterface
     */
    private $_builder;

    /**
     * Array of indices.
     *
     * @var array
     */
    protected $_indices = array();

    /**
     * Array of types.
     *
     * @var array
     */
    protected $_types = array();

    /**
     * @var \Elastica\Query
     */
    protected $_query;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * Client object.
     *
     * @var \Elastica\Client
     */
    protected $_client;

    /**
     * Constructs search object.
     *
     * @param \Elastica\Client $client  Client object
     * @param BuilderInterface $builder
     */
    public function __construct(Client $client, BuilderInterface $builder = null)
    {
        $this->_builder = $builder ?: new DefaultBuilder();
        $this->_client = $client;
    }

    /**
     * Adds a index to the list.
     *
     * @param \Elastica\Index|string $index Index object or string
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        if (!is_scalar($index)) {
            throw new InvalidException('Invalid param type');
        }

        $this->_indices[] = (string) $index;

        return $this;
    }

    /**
     * Add array of indices at once.
     *
     * @param array $indices
     *
     * @return $this
     */
    public function addIndices(array $indices = array())
    {
        foreach ($indices as $index) {
            $this->addIndex($index);
        }

        return $this;
    }

    /**
     * Adds a type to the current search.
     *
     * @param \Elastica\Type|string $type Type name or object
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addType($type)
    {
        if ($type instanceof Type) {
            $type = $type->getName();
        }

        if (!is_string($type)) {
            throw new InvalidException('Invalid type type');
        }

        $this->_types[] = $type;

        return $this;
    }

    /**
     * Add array of types.
     *
     * @param array $types
     *
     * @return $this
     */
    public function addTypes(array $types = array())
    {
        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    /**
     * @param string|array|\Elastica\Query|\Elastica\Suggest|\Elastica\Query\AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        if ($query instanceof AbstractFilter) {
            trigger_error('Deprecated: Elastica\Search::setQuery() passing AbstractFilter is deprecated. Create query and use setPostFilter with AbstractQuery instead.', E_USER_DEPRECATED);
        }

        $this->_query = Query::create($query);

        return $this;
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
        $this->_options = array();

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
            case self::OPTION_SEARCH_TYPE:
            case self::OPTION_ROUTING:
            case self::OPTION_PREFERENCE:
            case self::OPTION_VERSION:
            case self::OPTION_TIMEOUT:
            case self::OPTION_FROM:
            case self::OPTION_SIZE:
            case self::OPTION_SCROLL:
            case self::OPTION_SCROLL_ID:
            case self::OPTION_SEARCH_TYPE_SUGGEST:
            case self::OPTION_SEARCH_IGNORE_UNAVAILABLE:
            case self::OPTION_QUERY_CACHE:
                return true;
        }

        throw new InvalidException('Invalid option '.$key);
    }

    /**
     * Return client object.
     *
     * @return \Elastica\Client Client object
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Return array of indices.
     *
     * @return array List of index names
     */
    public function getIndices()
    {
        return $this->_indices;
    }

    /**
     * @return bool
     */
    public function hasIndices()
    {
        return count($this->_indices) > 0;
    }

    /**
     * @param Index|string $index
     *
     * @return bool
     */
    public function hasIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        return in_array($index, $this->_indices);
    }

    /**
     * Return array of types.
     *
     * @return array List of types
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * @return bool
     */
    public function hasTypes()
    {
        return count($this->_types) > 0;
    }

    /**
     * @param \Elastica\Type|string $type
     *
     * @return bool
     */
    public function hasType($type)
    {
        if ($type instanceof Type) {
            $type = $type->getName();
        }

        return in_array($type, $this->_types);
    }

    /**
     * @return \Elastica\Query
     */
    public function getQuery()
    {
        if (null === $this->_query) {
            $this->_query = Query::create('');
        }

        return $this->_query;
    }

    /**
     * Creates new search object.
     *
     * @param \Elastica\SearchableInterface $searchObject
     *
     * @return Search
     */
    public static function create(SearchableInterface $searchObject)
    {
        return $searchObject->createSearch();
    }

    /**
     * Combines indices and types to the search request path.
     *
     * @return string Search path
     */
    public function getPath()
    {
        if (isset($this->_options[self::OPTION_SCROLL_ID])) {
            return '_search/scroll';
        }

        $indices = $this->getIndices();

        $path = '';
        $types = $this->getTypes();

        if (empty($indices)) {
            if (!empty($types)) {
                $path .= '_all';
            }
        } else {
            $path .= implode(',', $indices);
        }

        if (!empty($types)) {
            $path .= '/'.implode(',', $types);
        }

        // Add full path based on indices and types -> could be all
        return $path.'/_search';
    }

    /**
     * Search in the set indices, types.
     *
     * @param mixed     $query
     * @param int|array $options OPTIONAL Limit or associative array of options (option=>value)
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return \Elastica\ResultSet
     */
    public function search($query = '', $options = null)
    {
        $this->setOptionsAndQuery($options, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $params = $this->getOptions();

        // Send scroll_id via raw HTTP body to handle cases of very large (> 4kb) ids.
        if ('_search/scroll' == $path) {
            $data = $params[self::OPTION_SCROLL_ID];
            unset($params[self::OPTION_SCROLL_ID]);
        } else {
            $data = $query->toArray();
        }

        $response = $this->getClient()->request(
            $path,
            Request::GET,
            $data,
            $params
        );

        return $this->_builder->buildResultSet($response, $query);
    }

    /**
     * @param mixed $query
     * @param $fullResult (default = false) By default only the total hit count is returned. If set to true, the full ResultSet including aggregations is returned.
     *
     * @return int|ResultSet
     */
    public function count($query = '', $fullResult = false)
    {
        $this->setOptionsAndQuery(null, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $response = $this->getClient()->request(
            $path,
            Request::GET,
            $query->toArray(),
            array(self::OPTION_SEARCH_TYPE => self::OPTION_SEARCH_TYPE_COUNT)
        );
        $resultSet = $this->_builder->buildResultSet($response, $query);

        return $fullResult ? $resultSet : $resultSet->getTotalHits();
    }

    /**
     * @param array|int                    $options
     * @param string|array|\Elastica\Query $query
     *
     * @return $this
     */
    public function setOptionsAndQuery($options = null, $query = '')
    {
        if ('' != $query) {
            $this->setQuery($query);
        }

        if (is_int($options)) {
            $this->getQuery()->setSize($options);
        } elseif (is_array($options)) {
            if (isset($options['limit'])) {
                $this->getQuery()->setSize($options['limit']);
                unset($options['limit']);
            }
            if (isset($options['explain'])) {
                $this->getQuery()->setExplain($options['explain']);
                unset($options['explain']);
            }
            $this->setOptions($options);
        }

        return $this;
    }

    /**
     * @param Suggest $suggest
     *
     * @return $this
     */
    public function setSuggest(Suggest $suggest)
    {
        return $this->setOptionsAndQuery(array(self::OPTION_SEARCH_TYPE_SUGGEST => 'suggest'), $suggest);
    }

    /**
     * Returns the Scroll Iterator.
     *
     * @see Elastica\Scroll
     *
     * @param string $expiryTime
     *
     * @return Scroll
     */
    public function scroll($expiryTime = '1m')
    {
        return new Scroll($this, $expiryTime);
    }

    /**
     * Returns the ScanAndScroll Iterator.
     *
     * @see Elastica\ScanAndScroll
     *
     * @param string $expiryTime
     * @param int    $sizePerShard
     *
     * @return ScanAndScroll
     */
    public function scanAndScroll($expiryTime = '1m', $sizePerShard = 1000)
    {
        return new ScanAndScroll($this, $expiryTime, $sizePerShard);
    }

    /**
     * @return BuilderInterface
     */
    public function getResultSetBuilder()
    {
        return $this->_builder;
    }
}
