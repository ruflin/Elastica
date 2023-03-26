<?php

namespace Elastica;

use Elastica\Exception\ClientException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\ResponseException;
use Elastica\Query\AbstractQuery;
use Elastica\Query\MatchAll;
use Elastica\ResultSet\BuilderInterface;
use Elastica\ResultSet\DefaultBuilder;
use Elastica\Suggest\AbstractSuggest;

/**
 * Elastica search object.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 * @phpstan-import-type TCreateQueryArgs from Query
 */
class Search
{
    /*
     * Options
     */
    public const OPTION_SEARCH_TYPE = 'search_type';
    public const OPTION_ROUTING = 'routing';
    public const OPTION_PREFERENCE = 'preference';
    public const OPTION_VERSION = 'version';
    public const OPTION_TIMEOUT = 'timeout';
    public const OPTION_FROM = 'from';
    public const OPTION_SIZE = 'size';
    public const OPTION_SCROLL = 'scroll';
    public const OPTION_SCROLL_ID = 'scroll_id';
    public const OPTION_QUERY_CACHE = 'query_cache';
    public const OPTION_TERMINATE_AFTER = 'terminate_after';
    public const OPTION_SHARD_REQUEST_CACHE = 'request_cache';
    public const OPTION_FILTER_PATH = 'filter_path';
    public const OPTION_TYPED_KEYS = 'typed_keys';

    /*
     * Search types
     */
    public const OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH = 'dfs_query_then_fetch';
    public const OPTION_SEARCH_TYPE_QUERY_THEN_FETCH = 'query_then_fetch';
    public const OPTION_SEARCH_TYPE_SUGGEST = 'suggest';
    public const OPTION_SEARCH_IGNORE_UNAVAILABLE = 'ignore_unavailable';

    /**
     * Array of indices names.
     *
     * @var string[]
     */
    protected $_indices = [];

    /**
     * @var Query
     */
    protected $_query;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * Client object.
     *
     * @var Client
     */
    protected $_client;

    /**
     * @var BuilderInterface|null
     */
    private $builder;

    public function __construct(Client $client, ?BuilderInterface $builder = null)
    {
        $this->_client = $client;
        $this->builder = $builder ?: new DefaultBuilder();
    }

    /**
     * Adds a index to the list.
     *
     * @param Index $index Index object or string
     *
     * @throws InvalidException
     */
    public function addIndex($index): self
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        } else {
            \trigger_deprecation(
                'ruflin/elastica',
                '7.2.0',
                'Passing a string as 1st argument to "%s()" is deprecated, pass an Index instance or use "addIndexByName" instead. It will throw a %s in 8.0.',
                __METHOD__,
                \TypeError::class
            );
        }

        if (!\is_scalar($index)) {
            throw new InvalidException('Invalid param type');
        }

        return $this->addIndexByName((string) $index);
    }

    /**
     * Adds an index to the list.
     */
    public function addIndexByName(string $index): self
    {
        $this->_indices[] = $index;

        return $this;
    }

    /**
     * Add array of indices at once.
     *
     * @param Index[] $indices
     */
    public function addIndices(array $indices = []): self
    {
        foreach ($indices as $index) {
            if (\is_string($index)) {
                \trigger_deprecation(
                    'ruflin/elastica',
                    '7.2.0',
                    'Passing a array of strings as 1st argument to "%s()" is deprecated, pass an array of Indexes or use "addIndicesByName" instead. It will throw a %s in 8.0.',
                    __METHOD__,
                    \TypeError::class
                );
                $this->addIndexByName($index);

                continue;
            }

            if (!$index instanceof Index) {
                throw new InvalidException('Invalid param type for addIndices(), expected Index[]');
            }

            $this->addIndex($index);
        }

        return $this;
    }

    /**
     * @param string[] $indices
     */
    public function addIndicesByName(array $indices = []): self
    {
        foreach ($indices as $index) {
            if (!\is_string($index)) {
                throw new InvalidException('Invalid param type for addIndicesByName(), expected string[]');
            }
            $this->addIndexByName($index);
        }

        return $this;
    }

    /**
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query
     * @phpstan-param TCreateQueryArgs $query
     */
    public function setQuery($query): self
    {
        $this->_query = Query::create($query);

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $key, $value): self
    {
        $this->validateOption($key);

        $this->_options[$key] = $value;

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->clearOptions();

        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }

        return $this;
    }

    public function clearOptions(): self
    {
        $this->_options = [];

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addOption(string $key, $value): self
    {
        $this->validateOption($key);

        $this->_options[$key][] = $value;

        return $this;
    }

    public function hasOption(string $key): bool
    {
        return isset($this->_options[$key]);
    }

    /**
     * @throws InvalidException if the given key does not exists as an option
     *
     * @return mixed
     */
    public function getOption(string $key)
    {
        if (!$this->hasOption($key)) {
            throw new InvalidException('Option '.$key.' does not exist');
        }

        return $this->_options[$key];
    }

    public function getOptions(): array
    {
        return $this->_options;
    }

    /**
     * Return client object.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Return array of indices names.
     *
     * @return string[]
     */
    public function getIndices(): array
    {
        return $this->_indices;
    }

    public function hasIndices(): bool
    {
        return \count($this->_indices) > 0;
    }

    /**
     * @param Index $index
     */
    public function hasIndex($index): bool
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        } else {
            \trigger_deprecation(
                'ruflin/elastica',
                '7.2.0',
                'Passing a string as 1st argument to "%s()" is deprecated, pass an Index instance or use "hasIndexByName" instead. It will throw a %s in 8.0.',
                __METHOD__,
                \TypeError::class
            );
        }

        return $this->hasIndexByName($index);
    }

    public function hasIndexByName(string $index): bool
    {
        return \in_array($index, $this->_indices, true);
    }

    public function getQuery(): Query
    {
        if (null === $this->_query) {
            $this->_query = new Query(new MatchAll());
        }

        return $this->_query;
    }

    /**
     * Creates new search object.
     */
    public static function create(SearchableInterface $searchObject): Search
    {
        return $searchObject->createSearch();
    }

    /**
     * Combines indices to the search request path.
     */
    public function getPath(): string
    {
        if (isset($this->_options[self::OPTION_SCROLL_ID])) {
            return '_search/scroll';
        }

        return \implode(',', $this->getIndices()).'/_search';
    }

    /**
     * Search in the set indices.
     *
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query
     * @phpstan-param TCreateQueryArgs $query
     *
     * @param array|int $options Limit or associative array of options (option=>value)
     *
     * @throws InvalidException
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function search($query = '', $options = null, string $method = Request::POST): ResultSet
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

        $response = $this->getClient()->request($path, $method, $data, $params);

        return $this->builder->buildResultSet($response, $query);
    }

    /**
     * @param array|Query|Query\AbstractQuery|string $query
     * @param bool                                   $fullResult By default only the total hit count is returned. If set to true, the full ResultSet including aggregations is returned
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     *
     * @return int|ResultSet
     */
    public function count($query = '', bool $fullResult = false, string $method = Request::POST)
    {
        $this->setOptionsAndQuery(null, $query);

        // Clone the object as we do not want to modify the original query.
        $query = clone $this->getQuery();
        $query->setSize(0);
        $query->setTrackTotalHits(true);

        $path = $this->getPath();

        $response = $this->getClient()->request(
            $path,
            $method,
            $query->toArray(),
            [self::OPTION_SEARCH_TYPE => self::OPTION_SEARCH_TYPE_QUERY_THEN_FETCH]
        );
        $resultSet = $this->builder->buildResultSet($response, $query);

        return $fullResult ? $resultSet : $resultSet->getTotalHits();
    }

    /**
     * @param array|int                                                              $options
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query
     * @phpstan-param TCreateQueryArgs $query
     */
    public function setOptionsAndQuery($options = null, $query = ''): self
    {
        if ('' !== $query) {
            $this->setQuery($query);
        }

        if (\is_int($options)) {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Passing an int as 1st argument to "%s()" is deprecated, pass an array with the key "size" instead. It will be removed in 8.0.', __METHOD__);
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

    public function setSuggest(Suggest $suggest): self
    {
        return $this->setOptionsAndQuery([self::OPTION_SEARCH_TYPE_SUGGEST => 'suggest'], $suggest);
    }

    /**
     * Returns the Scroll Iterator.
     *
     * @see Scroll
     */
    public function scroll(string $expiryTime = '1m'): Scroll
    {
        return new Scroll($this, $expiryTime);
    }

    public function getResultSetBuilder(): BuilderInterface
    {
        return $this->builder;
    }

    /**
     * @throws InvalidException If the given key is not a valid option
     */
    protected function validateOption(string $key): void
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
                return;
        }

        throw new InvalidException('Invalid option '.$key);
    }
}
