<?php

namespace Elastica;

use Elastica\Exception\InvalidException;
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
    const OPTION_TERMINATE_AFTER = 'terminate_after';
    const OPTION_SHARD_REQUEST_CACHE = 'request_cache';
    const OPTION_FILTER_PATH = 'filter_path';
    const OPTION_TYPED_KEYS = 'typed_keys';

    /*
     * Search types
     */
    const OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH = 'dfs_query_then_fetch';
    const OPTION_SEARCH_TYPE_QUERY_THEN_FETCH = 'query_then_fetch';
    const OPTION_SEARCH_TYPE_SUGGEST = 'suggest';
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
    protected $_indices = [];

    /**
     * Array of types.
     *
     * @var array
     */
    protected $_types = [];

    /**
     * @var \Elastica\Query
     */
    protected $_query;

    /**
     * @var array
     */
    protected $_options = [];

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
     * @param Index|string $index Index object or string
     *
     * @throws InvalidException
     *
     * @return $this
     */
    public function addIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        if (!\is_scalar($index)) {
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
    public function addIndices(array $indices = [])
    {
        foreach ($indices as $index) {
            $this->addIndex($index);
        }

        return $this;
    }

    /**
     * @param string|array|Query|Suggest|Query\AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
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
     * @throws InvalidException
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
     * @throws InvalidException
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
            case self::OPTION_TERMINATE_AFTER:
            case self::OPTION_SHARD_REQUEST_CACHE:
            case self::OPTION_FILTER_PATH:
            case self::OPTION_TYPED_KEYS:
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
        return \count($this->_indices) > 0;
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

        return \in_array($index, $this->_indices);
    }

    /**
     * @return Query
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
     * @param SearchableInterface $searchObject
     *
     * @return Search
     */
    public static function create(SearchableInterface $searchObject)
    {
        return $searchObject->createSearch();
    }

    /**
     * Combines indices to the search request path.
     *
     * @return string Search path
     */
    public function getPath()
    {
        if (isset($this->_options[self::OPTION_SCROLL_ID])) {
            return '_search/scroll';
        }

        return \implode(',', $this->getIndices()).'/_search';
    }

    /**
     * Search in the set indices.
     *
     * @param mixed     $query
     * @param int|array $options OPTIONAL Limit or associative array of options (option=>value)
     * @param string    $method  OPTIONAL Request method (use const's) (default = Request::POST)
     *
     * @throws InvalidException
     *
     * @return ResultSet
     */
    public function search($query = '', $options = null, $method = Request::POST)
    {
        $this->setOptionsAndQuery($options, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $params = $this->getOptions();

        // Send scroll_id via raw HTTP body to handle cases of very large (> 4kb) ids.
        if ('_search/scroll' === $path) {
            $data = [self::OPTION_SCROLL_ID => $params[self::OPTION_SCROLL_ID]];
            unset($params[self::OPTION_SCROLL_ID]);
        } else {
            $data = $query->toArray();
        }

        $response = $this->getClient()->request(
            $path,
            $method,
            $data,
            $params
        );

        return $this->_builder->buildResultSet($response, $query);
    }

    /**
     * @param mixed $query
     * @param $fullResult (default = false) By default only the total hit count is returned. If set to true, the full ResultSet including aggregations is returned
     * @param string $method OPTIONAL Request method (use const's) (default = Request::POST)
     *
     * @return int|ResultSet
     */
    public function count($query = '', $fullResult = false, $method = Request::POST)
    {
        $this->setOptionsAndQuery(null, $query);

        // Clone the object as we do not want to modify the original query.
        $query = clone $this->getQuery();
        $query->setSize(0);
        $path = $this->getPath();

        $response = $this->getClient()->request(
            $path,
            $method,
            $query->toArray(),
            [self::OPTION_SEARCH_TYPE => self::OPTION_SEARCH_TYPE_QUERY_THEN_FETCH]
        );
        $resultSet = $this->_builder->buildResultSet($response, $query);

        return $fullResult ? $resultSet : $resultSet->getTotalHits();
    }

    /**
     * @param array|int          $options
     * @param string|array|Query $query
     *
     * @return $this
     */
    public function setOptionsAndQuery($options = null, $query = '')
    {
        if ('' !== $query) {
            $this->setQuery($query);
        }

        if (\is_int($options)) {
            $this->getQuery()->setSize($options);
        } elseif (\is_array($options)) {
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
        return $this->setOptionsAndQuery([self::OPTION_SEARCH_TYPE_SUGGEST => 'suggest'], $suggest);
    }

    /**
     * Returns the Scroll Iterator.
     *
     * @see Scroll
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
     * @return BuilderInterface
     */
    public function getResultSetBuilder()
    {
        return $this->_builder;
    }
}
