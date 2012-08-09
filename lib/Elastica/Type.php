<?php
/**
 * Elastica type object
 *
 * elasticsearch has for every types as a substructure. This object
 * represents a type inside a context
 * The hierarchy is as following: client -> index -> type -> document
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Type implements Elastica_Searchable
{
    /**
     * Index
     *
     * @var Elastica_Index Index object
     */
    protected $_index = null;

    /**
     * Type name
     *
     * @var string Type name
     */
    protected $_name = '';

    /**
     * Creates a new type object inside the given index
     *
     * @param Elastica_Index $index Index Object
     * @param string         $name  Type name
     */
    public function __construct(Elastica_Index $index, $name)
    {
        $this->_index = $index;
        $this->_name = $name;
    }

    /**
     * Adds the given document to the search index
     *
     * @param  Elastica_Document $doc Document with data
     * @return Elastica_Response
     */
    public function addDocument(Elastica_Document $doc)
    {
        $path = $doc->getId();

        $query = array();

        if ($doc->getVersion() > 0) {
            $query['version'] = $doc->getVersion();
        }

        if (!is_null($doc->getParent())) {
            $query['parent'] = $doc->getParent();
        }

        if ($doc->getOpType()) {
            $query['op_type'] = $doc->getOpType();
        }

        if ($doc->getPercolate()) {
            $query['percolate'] = $doc->getPercolate();
        }

        $type = Elastica_Request::PUT;

        // If id is empty, POST has to be used to automatically create id
        if (empty($path)) {
            $type = Elastica_Request::POST;
        }

        return $this->request($path, $type, $doc->getData(), $query);
    }

    /**
     * Update document, using update script. Requires elasticsearch >= 0.19.0
     *
     * @param  string            $id      Document id
     * @param  Elastica_Script   $script  script to use for update
     * @param  array             $options options for query
     * @return Elastica_Response
     * @see  Elastica_Client::updateDocument()
     * @link http://www.elasticsearch.org/guide/reference/api/update.html
     */
    public function updateDocument($id, Elastica_Script $script, array $options = array())
    {
        return $this->getIndex()->updateDocument($id, $script, $this->getName(), $options);
    }

    /**
     * Uses _bulk to send documents to the server
     *
     * @param array $docs Array of Elastica_Document
     * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
     */
    public function addDocuments(array $docs)
    {
        foreach ($docs as $doc) {
            $doc->setType($this->getName());
        }

        return $this->getIndex()->addDocuments($docs);
    }

    /**
     * Get the document from search index
     *
     * @param  string            $id Document id
     * @return Elastica_Document
     */
    public function getDocument($id)
    {
        $path = $id;

        try {
            $result = $this->request($path, Elastica_Request::GET)->getData();
        } catch (Elastica_Exception_Response $e) {
            throw new Elastica_Exception_NotFound('doc id ' . $id . ' not found');
        }

        if (empty($result['exists'])) {
            throw new Elastica_Exception_NotFound('doc id ' . $id . ' not found');
        }

        $data = isset($result['_source']) ? $result['_source'] : array();
        $document = new Elastica_Document($id, $data, $this->getName(), $this->getIndex());
        $document->setVersion($result['_version']);

        return $document;
    }

    /**
     * Returns the type name
     *
     * @return string Type
     * @deprecated Use getName instead
     */
    public function getType()
    {
        return $this->getName();
    }

    /**
     * Returns the type name
     *
     * @return string Type name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets value type mapping for this type
     *
     * @param Elastica_Type_Mapping|array $mapping Elastica_Type_Mapping object or property array with all mappings
     */
    public function setMapping($mapping)
    {
        $mapping = Elastica_Type_Mapping::create($mapping);
        $mapping->setType($this);

        return $mapping->send();
    }

    /**
     * Returns current mapping for the given type
     *
     * @return array Current mapping
     */
    public function getMapping()
    {
        $path = '_mapping';

        $response = $this->request($path, Elastica_Request::GET);

        return $response->getData();
    }

    /**
     * Do a search on this type
     *
     * @param  string|array|Elastica_Query $query   Array with all query data inside or a Elastica_Query object
     * @param  int|array                   $options OPTIONAL Limit or associative array of options (option=>value)
     * @return Elastica_ResultSet          ResultSet with all results inside
     * @see Elastica_Searchable::search
     */
    public function search($query, $options = null)
    {
        $search = new Elastica_Search($this->getIndex()->getClient());
        $search->addIndex($this->getIndex());
        $search->addType($this);

        return $search->search($query, $options);
    }

    /**
     * Count docs by query
     *
     * @param  string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
     * @return int                         number of documents matching the query
     * @see Elastica_Searchable::count
     */
    public function count($query = '')
    {
        $query = Elastica_Query::create($query);
        $path = '_search';

        $response = $this->request($path, Elastica_Request::GET, $query->toArray(), array('search_type' => 'count'));
        $resultSet = new Elastica_ResultSet($response);

        return $resultSet->getTotalHits();
    }

    /**
     * Returns index client
     *
     * @return Elastica_Index Index object
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * Deletes an entry by its unique identifier
     *
     * @param  int|string        $id Document id
     * @return Elastica_Response Response object
     * @link http://www.elasticsearch.org/guide/reference/api/delete.html
     */
    public function deleteById($id)
    {
        if (empty($id) || !trim($id)) {
            throw new InvalidArgumentException();
        }

        return $this->request($id, Elastica_Request::DELETE);
    }

    /**
     * Deletes the given list of ids from this type
     *
     * @param  array             $ids
     * @return Elastica_Response Response object
     */
    public function deleteIds(array $ids)
    {
        return $this->getIndex()->getClient()->deleteIds($ids, $this->getIndex(), $this);
    }

    /**
     * Deletes entries in the db based on a query
     *
     * @param Elastica_Query|string $query Query object
     * @link http://www.elasticsearch.org/guide/reference/api/delete-by-query.html
     */
    public function deleteByQuery($query)
    {
        $query = Elastica_Query::create($query);

        return $this->request('_query', Elastica_Request::DELETE, $query->getQuery());
    }

    /**
     * Deletes the index type.
     *
     * @return Elastica_Response
     */
    public function delete()
    {
        $response = $this->request('', Elastica_Request::DELETE);

        return $response;
    }

    /**
     * More like this query based on the given object
     *
     * The id in the given object has to be set
     *
     * @param  Elastica_Document  $doc    Document to query for similar objects
     * @param  array              $params OPTIONAL Additional arguments for the query
     * @param  Elastica_Query     $query  OPTIONAL Query to filter the moreLikeThis results
     * @return Elastica_ResultSet ResultSet with all results inside
     * @link http://www.elasticsearch.org/guide/reference/api/more-like-this.html
     */
    public function moreLikeThis(Elastica_Document $doc, $params = array(), $query = array())
    {
        $path = $doc->getId() . '/_mlt';

        $query = Elastica_Query::create($query);

        $response = $this->request($path, Elastica_Request::GET, $query->toArray(), $params);

        return new Elastica_ResultSet($response);
    }

    /**
     * Makes calls to the elasticsearch server based on this type
     *
     * @param  string            $path   Path to call
     * @param  string            $method Rest method to use (GET, POST, DELETE, PUT)
     * @param  array             $data   OPTIONAL Arguments as array
     * @param  array             $query  OPTIONAL Query params
     * @return Elastica_Response Response object
     */
    public function request($path, $method, $data = array(), array $query = array())
    {
        $path = $this->getName() . '/' . $path;

        return $this->getIndex()->request($path, $method, $data, $query);
    }
}
