<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;
use Elastica\Query\MatchAll;
use Elastica\ResultSet\BuilderInterface;
use Elastica\ResultSet\DefaultBuilder;
use Elastica\Suggest\AbstractSuggest;

/**
 * Elastica search object.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 *
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
     */
    public function addIndex(Index $index): self
    {
        return $this->addIndexByName($index->getName());
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
     *
     * @phpstan-param TCreateQueryArgs $query
     */
    public function setQuery($query): self
    {
        $this->_query = Query::create($query);

        return $this;
    }

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

    public function hasIndex(Index $index): bool
    {
        return $this->hasIndexByName($index->getName());
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
     * @param array<string, mixed>|null                                              $options associative array of options (option=>value)
     *
     * @phpstan-param TCreateQueryArgs $query
     *
     * @throws InvalidException
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function search($query = '', ?array $options = null): ResultSet
    {
        $this->setOptionsAndQuery($options, $query);

        $query = $this->getQuery();
        $path = $this->getPath();

        $params = $this->getOptions();

        // Send scroll_id via raw HTTP body to handle cases of very large (> 4kb) ids.
        if ('_search/scroll' === $path) {
            $params['body'] = [self::OPTION_SCROLL_ID => $params[self::OPTION_SCROLL_ID]];
            unset($params[self::OPTION_SCROLL_ID]);

            $response = $this->getClient()->scroll($params);
        } else {
            if ($indices = $this->getIndices()) {
                $params['index'] = \implode(',', $indices);
            }
            $params['body'] = $query->toArray();

            $response = $this->getClient()->search($params);
        }

        return $this->builder->buildResultSet(new Response($response->asArray(), $response->getStatusCode()), $query);
    }

    /**
     * @param AbstractQuery|array|Query|string $query
     * @param bool                             $fullResult By default only the total hit count is returned. If set to true, the full ResultSet including aggregations is returned
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return int|ResultSet
     *
     * @phpstan-return ($fullResult is false ? int : ResultSet)
     */
    public function count($query = '', bool $fullResult = false)
    {
        $this->setOptionsAndQuery(null, $query);

        // Clone the object as we do not want to modify the original query.
        $query = clone $this->getQuery();
        $query->setSize(0);
        $query->setTrackTotalHits(true);

        $params = [
            'body' => $query->toArray(),
            [self::OPTION_SEARCH_TYPE => self::OPTION_SEARCH_TYPE_QUERY_THEN_FETCH],
        ];

        if ($indices = $this->getIndices()) {
            $params['index'] = \implode(',', $indices);
        }

        $response = $this->getClient()->search($params);

        $resultSet = $this->builder->buildResultSet(new Response($response->asArray(), $response->getStatusCode()), $query);

        return $fullResult ? $resultSet : $resultSet->getTotalHits();
    }

    /**
     * @param array<string, mixed>|null                                              $options
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query
     *
     * @phpstan-param TCreateQueryArgs $query
     */
    public function setOptionsAndQuery(?array $options = null, $query = ''): self
    {
        if ('' !== $query) {
            $this->setQuery($query);
        }

        if (null !== $options) {
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
