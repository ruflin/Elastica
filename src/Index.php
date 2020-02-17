<?php

namespace Elastica;

use Elastica\Bulk\ResponseSet;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elastica\Index\Recovery as IndexRecovery;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Index\Stats as IndexStats;
use Elastica\Query\AbstractQuery;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Script\AbstractScript;
use Elasticsearch\Endpoints\AbstractEndpoint;
use Elasticsearch\Endpoints\DeleteByQuery;
use Elasticsearch\Endpoints\Get as DocumentGet;
use Elasticsearch\Endpoints\Index as IndexEndpoint;
use Elasticsearch\Endpoints\Indices\Alias;
use Elasticsearch\Endpoints\Indices\Aliases\Update;
use Elasticsearch\Endpoints\Indices\Analyze;
use Elasticsearch\Endpoints\Indices\Cache\Clear;
use Elasticsearch\Endpoints\Indices\Close;
use Elasticsearch\Endpoints\Indices\Create;
use Elasticsearch\Endpoints\Indices\Delete;
use Elasticsearch\Endpoints\Indices\Exists;
use Elasticsearch\Endpoints\Indices\Flush;
use Elasticsearch\Endpoints\Indices\ForceMerge;
use Elasticsearch\Endpoints\Indices\Mapping\Get as MappingGet;
use Elasticsearch\Endpoints\Indices\Open;
use Elasticsearch\Endpoints\Indices\Refresh;
use Elasticsearch\Endpoints\Indices\Settings\Put;
use Elasticsearch\Endpoints\UpdateByQuery;

/**
 * Elastica index object.
 *
 * Handles reads, deletes and configurations of an index
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
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
     * @return \Elastica\Index\Stats
     */
    public function getStats()
    {
        return new IndexStats($this);
    }

    /**
     * Return Index Recovery.
     *
     * @return \Elastica\Index\Recovery
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
     */
    public function getMapping(): array
    {
        $response = $this->requestEndpoint(new MappingGet());
        $data = $response->getData();

        // Get first entry as if index is an Alias, the name of the mapping is the real name and not alias name
        $mapping = \array_shift($data);

        return $mapping['mappings'] ?? [];
    }

    /**
     * Returns the index settings object.
     *
     * @return \Elastica\Index\Settings Settings object
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
     * @param array|Query|string $query   Query object or array
     * @param AbstractScript     $script  Script
     * @param array              $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update-by-query.html
     */
    public function updateByQuery($query, AbstractScript $script, array $options = []): Response
    {
        $endpoint = new UpdateByQuery();
        $q = Query::create($query)->getQuery();
        $body = [
            'query' => \is_array($q) ? $q : $q->toArray(),
            'script' => $script->toArray()['script'],
        ];

        $endpoint->setBody($body);
        $endpoint->setParams($options);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Adds the given document to the search index.
     */
    public function addDocument(Document $doc): Response
    {
        $endpoint = new IndexEndpoint();

        if (null !== $doc->getId() && '' !== $doc->getId()) {
            $endpoint->setID($doc->getId());
        }

        $options = $doc->getOptions(
            [
                'version',
                'version_type',
                'routing',
                'percolate',
                'parent',
                'op_type',
                'consistency',
                'replication',
                'refresh',
                'timeout',
                'pipeline',
            ]
        );

        $endpoint->setBody($doc->getData());
        $endpoint->setParams($options);

        $response = $this->requestEndpoint($endpoint);

        $data = $response->getData();
        // set autogenerated id to document
        if ($response->isOk() && (
            $doc->isAutoPopulate() || $this->getClient()->getConfigValue(['document', 'autoPopulate'], false)
        )) {
            if (isset($data['_id']) && !$doc->hasId()) {
                $doc->setId($data['_id']);
            }
            if (isset($data['_version'])) {
                $doc->setVersion($data['_version']);
            }
        }

        return $response;
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * @param array|Document[] $docs    Array of Elastica\Document
     * @param array            $options Array of query params to use for query. For possible options check es api
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
     * @throws \Elastica\Exception\ResponseException
     * @throws NotFoundException
     */
    public function getDocument($id, array $options = []): Document
    {
        $endpoint = new DocumentGet();
        $endpoint->setID($id);
        $endpoint->setParams($options);

        $response = $this->requestEndpoint($endpoint);
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

        $document = new Document($id, $data, $this->getName());
        $document->setVersion($result['_version']);

        return $document;
    }

    /**
     * Deletes a document by its unique identifier.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete.html
     */
    public function deleteById(string $id, array $options = []): Response
    {
        if (!\trim($id)) {
            throw new NotFoundException('Doc id "'.$id.'" not found and can not be deleted');
        }

        $endpoint = new \Elasticsearch\Endpoints\Delete();
        $endpoint->setID(\trim($id));
        $endpoint->setParams($options);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Deletes documents matching the given query.
     *
     * @param AbstractQuery|array|Query|string $query   Query object or array
     * @param array                            $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
     */
    public function deleteByQuery($query, array $options = []): Response
    {
        $query = Query::create($query)->getQuery();

        $endpoint = new DeleteByQuery();
        $endpoint->setBody(['query' => \is_array($query) ? $query : $query->toArray()]);
        $endpoint->setParams($options);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Deletes the index.
     */
    public function delete(): Response
    {
        return $this->requestEndpoint(new Delete());
    }

    /**
     * Uses the "_bulk" endpoint to delete documents from the server.
     *
     * @param Document[] $docs Array of documents
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
     */
    public function forcemerge($args = []): Response
    {
        $endpoint = new ForceMerge();
        $endpoint->setParams($args);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Refreshes the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     */
    public function refresh(): Response
    {
        return $this->requestEndpoint(new Refresh());
    }

    /**
     * Creates a new index with the given arguments.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html
     *
     * @param array      $args    Additional arguments to pass to the Create endpoint
     * @param array|bool $options OPTIONAL
     *                            bool=> Deletes index first if already exists (default = false).
     *                            array => Associative array of options (option=>value)
     *
     * @throws InvalidException
     * @throws \Elastica\Exception\ResponseException
     *
     * @return Response Server response
     */
    public function create(array $args = [], $options = null): Response
    {
        if (\is_bool($options) && $options) {
            try {
                $this->delete();
            } catch (ResponseException $e) {
                // Table can't be deleted, because doesn't exist
            }
        } elseif (\is_array($options)) {
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'recreate':
                        try {
                            $this->delete();
                        } catch (ResponseException $e) {
                            // Table can't be deleted, because doesn't exist
                        }
                        break;
                    default:
                        throw new InvalidException('Invalid option '.$key);
                        break;
                }
            }
        }

        $endpoint = new Create();
        $endpoint->setBody($args);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Checks if the given index exists ans is created.
     */
    public function exists(): bool
    {
        $response = $this->requestEndpoint(new Exists());

        return 200 === $response->getStatus();
    }

    /**
     * @param array|Query|string $query
     * @param array|int          $options
     * @param BuilderInterface   $builder
     */
    public function createSearch($query = '', $options = null, ?BuilderInterface $builder = null): Search
    {
        $search = new Search($this->getClient(), $builder);
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    /**
     * Searches in this index.
     *
     * @param array|Query|string $query   Array with all query data inside or a Elastica\Query object
     * @param array|int          $options Limit or associative array of options (option=>value)
     * @param string             $method  Request method, see Request's constants
     *
     * @see \Elastica\SearchableInterface::search
     */
    public function search($query = '', $options = null, string $method = Request::POST): ResultSet
    {
        $search = $this->createSearch($query, $options);

        return $search->search('', null, $method);
    }

    /**
     * Counts results of query.
     *
     * @param array|Query|string $query  Array with all query data inside or a Elastica\Query object
     * @param string             $method Request method, see Request's constants
     *
     * @see \Elastica\SearchableInterface::count
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
     */
    public function open(): Response
    {
        return $this->requestEndpoint(new Open());
    }

    /**
     * Closes the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     */
    public function close(): Response
    {
        return $this->requestEndpoint(new Close());
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

        $endpoint = new Update();
        $endpoint->setBody($data);

        return $this->getClient()->requestEndpoint($endpoint);
    }

    /**
     * Removes an alias pointing to the current index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     */
    public function removeAlias(string $name): Response
    {
        $endpoint = new Alias\Delete();
        $endpoint->setName($name);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Returns all index aliases.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        $endpoint = new Alias\Get();
        $endpoint->setName('*');

        $responseData = $this->requestEndpoint($endpoint)->getData();

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
     */
    public function clearCache(): Response
    {
        // TODO: add additional cache clean arguments
        return $this->requestEndpoint(new Clear());
    }

    /**
     * Flushes the index to storage.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-flush.html
     */
    public function flush(array $options = []): Response
    {
        $endpoint = new Flush();
        $endpoint->setParams($options);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Can be used to change settings during runtime. One example is to use it for bulk updating.
     *
     * @param array $data Data array
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
     */
    public function setSettings(array $data): Response
    {
        $endpoint = new Put();
        $endpoint->setBody($data);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Makes calls to the elasticsearch server based on this index.
     *
     * @param string       $path   Path to call
     * @param string       $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array|string $data   Arguments as array or encoded string
     */
    public function request(string $path, string $method, $data = [], array $queryParameters = []): Response
    {
        $path = $this->getName().'/'.$path;

        return $this->getClient()->request($path, $method, $data, $queryParameters);
    }

    /**
     * Makes calls to the elasticsearch server with usage official client Endpoint based on this index.
     */
    public function requestEndpoint(AbstractEndpoint $endpoint): Response
    {
        $cloned = clone $endpoint;
        $cloned->setIndex($this->getName());

        return $this->getClient()->requestEndpoint($cloned);
    }

    /**
     * Run the analysis on the index.
     *
     * @param array $body request body for the `_analyze` API, see API documentation for the required properties
     * @param array $args Additional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-analyze.html
     */
    public function analyze(array $body, $args = []): array
    {
        $endpoint = new Analyze();
        $endpoint->setBody($body);
        $endpoint->setParams($args);

        $data = $this->requestEndpoint($endpoint)->getData();

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
