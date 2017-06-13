<?php
namespace Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Exception\ResponseException;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Index\Stats as IndexStats;
use Elastica\ResultSet\BuilderInterface;
use Elasticsearch\Endpoints\AbstractEndpoint;
use Elasticsearch\Endpoints\DeleteByQuery;
use Elasticsearch\Endpoints\Indices\Aliases\Update;
use Elasticsearch\Endpoints\Indices\Analyze;
use Elasticsearch\Endpoints\Indices\Cache\Clear;
use Elasticsearch\Endpoints\Indices\Close;
use Elasticsearch\Endpoints\Indices\Create;
use Elasticsearch\Endpoints\Indices\Delete;
use Elasticsearch\Endpoints\Indices\Exists;
use Elasticsearch\Endpoints\Indices\Flush;
use Elasticsearch\Endpoints\Indices\ForceMerge;
use Elasticsearch\Endpoints\Indices\Mapping\Get;
use Elasticsearch\Endpoints\Indices\Open;
use Elasticsearch\Endpoints\Indices\Refresh;
use Elasticsearch\Endpoints\Indices\Settings\Put;

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
     * @var \Elastica\Client Client object
     */
    protected $_client;

    /**
     * Creates a new index object.
     *
     * All the communication to and from an index goes of this object
     *
     * @param \Elastica\Client $client Client object
     * @param string           $name   Index name
     */
    public function __construct(Client $client, $name)
    {
        $this->_client = $client;

        if (!is_scalar($name)) {
            throw new InvalidException('Index name should be a scalar type');
        }
        $this->_name = (string) $name;
    }

    /**
     * Returns a type object for the current index with the given name.
     *
     * @param string $type Type name
     *
     * @return \Elastica\Type Type object
     */
    public function getType($type)
    {
        return new Type($this, $type);
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
     * Gets all the type mappings for an index.
     *
     * @return array
     */
    public function getMapping()
    {
        $response = $this->requestEndpoint(new Get());
        $data = $response->getData();

        // Get first entry as if index is an Alias, the name of the mapping is the real name and not alias name
        $mapping = array_shift($data);

        if (isset($mapping['mappings'])) {
            return $mapping['mappings'];
        }

        return [];
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
     * Uses _bulk to send documents to the server.
     *
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @return \Elastica\Bulk\ResponseSet
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function updateDocuments(array $docs)
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->updateDocuments($docs);
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @return \Elastica\Bulk\ResponseSet
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function addDocuments(array $docs)
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->addDocuments($docs);
    }

    /**
     * Deletes entries in the db based on a query.
     *
     * @param \Elastica\Query|string|array $query   Query object or array
     * @param array                        $options Optional params
     *
     * @return \Elastica\Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/docs-delete-by-query.html
     */
    public function deleteByQuery($query, array $options = [])
    {
        $query = Query::create($query)->getQuery();

        $endpoint = new DeleteByQuery();
        $endpoint->setBody(['query' => is_array($query) ? $query : $query->toArray()]);
        $endpoint->setParams($options);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Deletes the index.
     *
     * @return \Elastica\Response Response object
     */
    public function delete()
    {
        return $this->requestEndpoint(new Delete());
    }

    /**
     * Uses _bulk to delete documents from the server.
     *
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @return \Elastica\Bulk\ResponseSet
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function deleteDocuments(array $docs)
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->deleteDocuments($docs);
    }

    /**
     * Optimizes search index.
     *
     * Detailed arguments can be found here in the link
     *
     * @param array $args OPTIONAL Additional arguments
     *
     * @return array Server response
     *
     * @deprecated Replaced by forcemerge
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-optimize.html
     */
    public function optimize($args = [])
    {
        trigger_error('Deprecated: Elastica\Index::optimize() is deprecated and will be removed in further Elastica releases. Use Elastica\Index::forcemerge() instead.', E_USER_DEPRECATED);

        return $this->forcemerge($args);
    }

    /**
     * Force merges index.
     *
     * Detailed arguments can be found here in the link
     *
     * @param array $args OPTIONAL Additional arguments
     *
     * @return Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-forcemerge.html
     */
    public function forcemerge($args = [])
    {
        $endpoint = new ForceMerge();
        $endpoint->setParams($args);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Refreshes the index.
     *
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     */
    public function refresh()
    {
        return $this->requestEndpoint(new Refresh());
    }

    /**
     * Creates a new index with the given arguments.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html
     *
     * @param array      $args    OPTIONAL Arguments to use
     * @param bool|array $options OPTIONAL
     *                            bool=> Deletes index first if already exists (default = false).
     *                            array => Associative array of options (option=>value)
     *
     * @throws \Elastica\Exception\InvalidException
     * @throws \Elastica\Exception\ResponseException
     *
     * @return \Elastica\Response Server response
     */
    public function create(array $args = [], $options = null)
    {
        if (is_bool($options) && $options) {
            try {
                $this->delete();
            } catch (ResponseException $e) {
                // Table can't be deleted, because doesn't exist
            }
        } elseif (is_array($options)) {
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
     * Checks if the given index is already created.
     *
     * @return bool True if index exists
     */
    public function exists()
    {
        $response = $this->requestEndpoint(new Exists());

        return $response->getStatus() === 200;
    }

    /**
     * @param string|array|\Elastica\Query $query
     * @param int|array                    $options
     * @param BuilderInterface             $builder
     *
     * @return Search
     */
    public function createSearch($query = '', $options = null, BuilderInterface $builder = null)
    {
        $search = new Search($this->getClient(), $builder);
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    /**
     * Searches in this index.
     *
     * @param string|array|\Elastica\Query $query   Array with all query data inside or a Elastica\Query object
     * @param int|array                    $options OPTIONAL Limit or associative array of options (option=>value)
     *
     * @return \Elastica\ResultSet with all results inside
     *
     * @see \Elastica\SearchableInterface::search
     */
    public function search($query = '', $options = null)
    {
        $search = $this->createSearch($query, $options);

        return $search->search();
    }

    /**
     * Counts results of query.
     *
     * @param string|array|\Elastica\Query $query Array with all query data inside or a Elastica\Query object
     *
     * @return int number of documents matching the query
     *
     * @see \Elastica\SearchableInterface::count
     */
    public function count($query = '')
    {
        $search = $this->createSearch($query);

        return $search->count();
    }

    /**
     * Opens an index.
     *
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     */
    public function open()
    {
        return $this->requestEndpoint(new Open());
    }

    /**
     * Closes the index.
     *
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     */
    public function close()
    {
        return $this->requestEndpoint(new Close());
    }

    /**
     * Returns the index name.
     *
     * @return string Index name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns index client.
     *
     * @return \Elastica\Client Index client object
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Adds an alias to the current index.
     *
     * @param string $name    Alias name
     * @param bool   $replace OPTIONAL If set, an existing alias will be replaced
     *
     * @return \Elastica\Response Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     */
    public function addAlias($name, $replace = false)
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
     * @param string $name Alias name
     *
     * @return \Elastica\Response Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     */
    public function removeAlias($name)
    {
        $endpoint = new \Elasticsearch\Endpoints\Indices\Alias\Delete();
        $endpoint->setName($name);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Returns all index aliases.
     *
     * @return array Aliases
     */
    public function getAliases()
    {
        $endpoint = new \Elasticsearch\Endpoints\Indices\Alias\Get();
        $endpoint->setName('*');

        $responseData = $this->requestEndpoint($endpoint)->getData();

        if (!isset($responseData[$this->getName()])) {
            return [];
        }

        $data = $responseData[$this->getName()];
        if (!empty($data['aliases'])) {
            return array_keys($data['aliases']);
        }

        return [];
    }

    /**
     * Checks if the index has the given alias.
     *
     * @param string $name Alias name
     *
     * @return bool
     */
    public function hasAlias($name)
    {
        return in_array($name, $this->getAliases());
    }

    /**
     * Clears the cache of an index.
     *
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-clearcache.html
     */
    public function clearCache()
    {
        // TODO: add additional cache clean arguments
        return $this->requestEndpoint(new Clear());
    }

    /**
     * Flushes the index to storage.
     *
     * @param array $options
     *
     * @return Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-flush.html
     */
    public function flush(array $options = [])
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
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
     */
    public function setSettings(array $data)
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
     * @param array|string $data   OPTIONAL Arguments as array or encoded string
     * @param array        $query  OPTIONAL Query params
     *
     * @return \Elastica\Response Response object
     */
    public function request($path, $method, $data = [], array $query = [])
    {
        $path = $this->getName().'/'.$path;

        return $this->getClient()->request($path, $method, $data, $query);
    }

    /**
     * Makes calls to the elasticsearch server with usage official client Endpoint based on this index.
     *
     * @param AbstractEndpoint $endpoint
     *
     * @return Response
     */
    public function requestEndpoint(AbstractEndpoint $endpoint)
    {
        $cloned = clone $endpoint;
        $cloned->setIndex($this->getName());

        return $this->getClient()->requestEndpoint($cloned);
    }

    /**
     * Analyzes a string.
     *
     * Detailed arguments can be found here in the link
     *
     * @param string $text String to be analyzed
     * @param array  $args OPTIONAL Additional arguments
     *
     * @return array Server response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-analyze.html
     */
    public function analyze($text, $args = [])
    {
        $endpoint = new Analyze();
        $endpoint->setBody($text);
        $endpoint->setParams($args);

        $data = $this->requestEndpoint($endpoint)->getData();

        // Support for "Explain" parameter, that returns a different response structure from Elastic
        // @see: https://www.elastic.co/guide/en/elasticsearch/reference/current/_explain_analyze.html
        if (isset($args['explain']) && $args['explain']) {
            return $data['detail'];
        }

        return $data['tokens'];
    }
}
