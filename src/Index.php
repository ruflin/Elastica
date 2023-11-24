<?php

namespace Elastica;

use Elastica\Bulk\ResponseSet;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use Elastica\Exception\ClientException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elastica\Index\Recovery as IndexRecovery;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Index\Stats as IndexStats;
use Elastica\Query\AbstractQuery;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Script\AbstractScript;

/**
 * Elastica index object.
 *
 * Handles reads, deletes and configurations of an index
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 *
 * @phpstan-import-type TCreateQueryArgsMatching from Query
 */
class Index implements SearchableInterface
{
    /**
     * Index name.
     *
     * @var string Index name
     */
    protected $_name;

    /**
     * Client object.
     *
     * @var Client Client object
     */
    protected $_client;

    /**
     * Creates a new index object.
     *
     * All the communication to and from an index goes of this object
     *
     * @param Client $client Client object
     * @param string $name   Index name
     */
    public function __construct(Client $client, string $name)
    {
        $this->_client = $client;
        $this->_name = $name;
    }

    /**
     * Return Index Stats.
     *
     * @return IndexStats
     */
    public function getStats()
    {
        return new IndexStats($this);
    }

    /**
     * Return Index Recovery.
     *
     * @return IndexRecovery
     */
    public function getRecovery()
    {
        return new IndexRecovery($this);
    }

    /**
     * Sets the mappings for the current index.
     *
     * @param Mapping $mapping MappingType object
     * @param array   $query   querystring when put mapping (for example update_all_types)
     */
    public function setMapping(Mapping $mapping, array $query = []): Response
    {
        return $mapping->send($this, $query);
    }

    /**
     * Gets all mappings for the current index.
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function getMapping(): array
    {
        /** @var Response $response */
        $response = $this->getClient()->indices()->getMapping(['index' => $this->getName()]);
        $data = $response->getData();

        // Get first entry as if index is an Alias, the name of the mapping is the real name and not alias name
        $mapping = \array_shift($data);

        return $mapping['mappings'] ?? [];
    }

    /**
     * Returns the index settings object.
     *
     * @return IndexSettings
     */
    public function getSettings()
    {
        return new IndexSettings($this);
    }

    /**
     * @param array|string $data
     *
     * @return Document
     */
    public function createDocument(string $id = '', $data = [])
    {
        return new Document($id, $data, $this);
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * @param Document[] $docs    Array of Elastica\Document
     * @param array      $options Array of query params to use for query. For possible options check es api
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     * @throws BulkResponseException
     * @throws InvalidException
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function updateDocuments(array $docs, array $options = []): ResponseSet
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->updateDocuments($docs, $options);
    }

    /**
     * Update entries in the db based on a query.
     *
     * @param AbstractQuery|array|Query|string|null $query Query object or array
     *
     * @phpstan-param TCreateQueryArgsMatching $query
     *
     * @param AbstractScript $script  Script
     * @param array          $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update-by-query.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function updateByQuery($query, AbstractScript $script, array $options = []): Response
    {
        $q = Query::create($query)->getQuery();
        $params = [
            'index' => $this->getName(),
            'body' => [
                'query' => \is_array($q) ? $q : $q->toArray(),
                'script' => $script->toArray()['script'],
            ]
        ];

        return $this->getClient()->updateByQuery(array_merge($params, $options));
    }

    /**
     * Adds the given document to the search index.
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function addDocument(Document $doc): Response
    {
        $params = ['index' => $this->getName()];

        if (null !== $doc->getId() && '' !== $doc->getId()) {
            $params['id'] = $doc->getId();
        }

        $options = $doc->getOptions(
            [
                'consistency',
                'op_type',
                'parent',
                'percolate',
                'pipeline',
                'refresh',
                'replication',
                'retry_on_conflict',
                'routing',
                'timeout',
            ]
        );

        $params['body'] = $doc->getData();
        $params = array_merge($params, $options);

        /** @var Response $response */
        $response = $this->getClient()->index($params);

        $data = $response->getData();
        // set autogenerated id to document
        if ($response->isOk() && (
            $doc->isAutoPopulate() || $this->getClient()->getConfigValue(['document', 'autoPopulate'], false)
        )) {
            if (isset($data['_id']) && !$doc->hasId()) {
                $doc->setId($data['_id']);
            }
            $doc->setVersionParams($data);
        }

        return $response;
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * @param array|Document[] $docs    Array of Elastica\Document
     * @param array            $options Array of query params to use for query. For possible options check es api
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     * @throws BulkResponseException
     * @throws InvalidException
     *
     * @return ResponseSet
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function addDocuments(array $docs, array $options = [])
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->addDocuments($docs, $options);
    }

    /**
     * Get the document from search index.
     *
     * @param int|string $id      Document id
     * @param array      $options options for the get request
     *
     * @throws NotFoundException
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function getDocument($id, array $options = []): Document
    {
        $params = array_merge([
            'id' => $id,
            'index' => $this->getName()
        ], $options);

        /** @var Response $response */
        $response = $this->getClient()->get($params);
        $result = $response->getData();

        if (!isset($result['found']) || false === $result['found']) {
            throw new NotFoundException('doc id '.$id.' not found');
        }

        if (isset($result['fields'])) {
            $data = $result['fields'];
        } elseif (isset($result['_source'])) {
            $data = $result['_source'];
        } else {
            $data = [];
        }

        $doc = new Document($id, $data, $this->getName());
        $doc->setVersionParams($result);

        return $doc;
    }

    /**
     * Deletes a document by its unique identifier.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function deleteById(string $id, array $options = []): Response
    {
        if (!\trim($id)) {
            throw new NotFoundException('Doc id "'.$id.'" not found and can not be deleted');
        }

        $params = [
            'id' => \trim($id),
            'index' => $this->getName()
        ];

        return $this->getClient()->delete(array_merge($params, $options));
    }

    /**
     * Deletes documents matching the given query.
     *
     * @param AbstractQuery|array|Query|string|null $query Query object or array
     *
     * @phpstan-param TCreateQueryArgsMatching $query
     *
     * @param array $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function deleteByQuery($query, array $options = []): Response
    {
        $query = Query::create($query)->getQuery();

        $params = array_merge([
            'index' => $this->getName(),
            'body' => ['query' => \is_array($query) ? $query : $query->toArray()]
        ], $options);

        return $this->getClient()->deleteByQuery($params);
    }

    /**
     * Opens a Point-in-Time on the index.
     *
     * @see: https://www.elastic.co/guide/en/elasticsearch/reference/current/point-in-time-api.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function openPointInTime(string $keepAlive): Response
    {
        return $this->getClient()->openPointInTime(['index' => $this->getName(), 'keep_alive' => $keepAlive]);
    }

    /**
     * Deletes the index.
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function delete(): Response
    {
        return $this->getClient()->indices()->delete(['index' => $this->getName()]);
    }

    /**
     * Uses the "_bulk" endpoint to delete documents from the server.
     *
     * @param Document[] $docs Array of documents
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     * @throws BulkResponseException
     * @throws InvalidException
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function deleteDocuments(array $docs): ResponseSet
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->deleteDocuments($docs);
    }

    /**
     * Force merges index.
     *
     * Detailed arguments can be found here in the ES documentation.
     *
     * @param array $args Additional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-forcemerge.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function forcemerge($args = []): Response
    {
        return $this->getClient()->indices()->forcemerge(array_merge(['index' => $this->getName(), $args]));
    }

    /**
     * Refreshes the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function refresh(): Response
    {
        return $this->getClient()->indices()->refresh();
    }

    /**
     * Creates a new index with the given arguments.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html
     *
     * @param array $args    Additional arguments to pass to the Create endpoint
     * @param array $options Associative array of options (option=>value)
     *
     * @throws InvalidException
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     *
     * @return Response Server response
     */
    public function create(array $args = [], array $options = []): Response
    {
        if ($options['recreate'] ?? false) {
            try {
                $this->delete();
            } catch (ResponseException $e) {
                // Index can't be deleted, because it doesn't exist
            }
        }

        unset($options['recreate']);

        $params = array_merge([
            'index' => $this->getName(),
            'body' => $args
        ], $options);

        return $this->getClient()->indices()->create($params);
    }

    /**
     * Checks if the given index exists ans is created.
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function exists(): bool
    {
        /** @var Response $response */
        $response = $this->getClient()->indices()->exists(['index' => $this->getName()]);

        return 200 === $response->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function createSearch($query = '', ?array $options = null, ?BuilderInterface $builder = null): Search
    {
        $search = new Search($this->getClient(), $builder);
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query = '', ?array $options = null, string $method = Request::POST): ResultSet
    {
        $search = $this->createSearch($query, $options);

        return $search->search('', null, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function count($query = '', string $method = Request::POST): int
    {
        $search = $this->createSearch($query);

        return $search->count('', false, $method);
    }

    /**
     * Opens an index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function open(): Response
    {
        return $this->getClient()->indices()->open(['index' => $this->getName()]);
    }

    /**
     * Closes the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function close(): Response
    {
        return $this->getClient()->indices()->close(['index' => $this->getName()]);
    }

    /**
     * Returns the index name.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Returns index client.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Adds an alias to the current index.
     *
     * @param bool $replace If set, an existing alias will be replaced
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function addAlias(string $name, bool $replace = false): Response
    {
        $data = ['actions' => []];

        if ($replace) {
            $status = new Status($this->getClient());
            foreach ($status->getIndicesWithAlias($name) as $index) {
                $data['actions'][] = ['remove' => ['index' => $index->getName(), 'alias' => $name]];
            }
        }

        $data['actions'][] = ['add' => ['index' => $this->getName(), 'alias' => $name]];

        // // TODO: Use only UpdateAliases when dropping support for elasticsearch/elasticsearch 7.x
        // $endpoint = \class_exists(UpdateAliases::class) ? new UpdateAliases() : new Update();
        // $endpoint->setBody($data);

        return $this->getClient()->indices()->updateAliases(['index' => $this->getName(), 'body' => $data]);
    }

    /**
     * Removes an alias pointing to the current index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function removeAlias(string $name): Response
    {
        return $this->getClient()->indices()->deleteAlias(['index' => $this->getName(), 'name' => $name]);
    }

    /**
     * Returns all index aliases.
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        /** @var Response $response */
        $response = ($this->getClient()->indices()->getAlias(['name' => '*']));
        $responseData = $response->getData();

        if (!isset($responseData[$this->getName()])) {
            return [];
        }

        $data = $responseData[$this->getName()];
        if (!empty($data['aliases'])) {
            return \array_keys($data['aliases']);
        }

        return [];
    }

    /**
     * Checks if the index has the given alias.
     */
    public function hasAlias(string $name): bool
    {
        return \in_array($name, $this->getAliases(), true);
    }

    /**
     * Clears the cache of an index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-clearcache.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function clearCache(): Response
    {
        // TODO: add additional cache clean arguments
        return $this->getClient()->indices()->clearCache();
    }

    /**
     * Flushes the index to storage.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-flush.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function flush(array $options = []): Response
    {
        return $this->getClient()->indices()->flush($options);
    }

    /**
     * Can be used to change settings during runtime. One example is to use it for bulk updating.
     *
     * @param array $data Data array
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function setSettings(array $data): Response
    {
        return $this->getClient()->indices()->putSettings(['index' => $this->getName(), 'body' => $data]);
    }

    /**
     * Makes calls to the elasticsearch server based on this index.
     *
     * @param string       $path   Path to call
     * @param string       $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array|string $data   Arguments as array or encoded string
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function request(string $path, string $method, $data = [], array $queryParameters = []): Response
    {
        $path = $this->getName().'/'.$path;

        return $this->getClient()->request($path, $method, $data, $queryParameters);
    }

    /**
     * Run the analysis on the index.
     *
     * @param array $body request body for the `_analyze` API, see API documentation for the required properties
     * @param array $args Additional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-analyze.html
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function analyze(array $body, $args = []): array
    {
        $params = array_merge([
            'index' => $this->getName(),
            'body' => $body
        ], $args);

        /** @var Response $response */
        $response = $this->getClient()->indices()->analyze($params);
        $data = $response->getData();

        // Support for "Explain" parameter, that returns a different response structure from Elastic
        // @see: https://www.elastic.co/guide/en/elasticsearch/reference/current/_explain_analyze.html
        if (isset($body['explain']) && $body['explain']) {
            return $data['detail'];
        }

        return $data['tokens'];
    }

    /**
     * Update document, using update script.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update.html
     *
     * @param AbstractScript|Document $data    Document or Script with update data
     * @param array                   $options array of query params to use for query
     */
    public function updateDocument($data, array $options = []): Response
    {
        if (!($data instanceof Document) && !($data instanceof AbstractScript)) {
            throw new \InvalidArgumentException('Data should be a Document or Script');
        }

        if (!$data->hasId()) {
            throw new InvalidException('Document or Script id is not set');
        }

        return $this->getClient()->updateDocument($data->getId(), $data, $this->getName(), $options);
    }
}
