<?php
namespace Elastica\Type;

use Elastica\Client;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\SearchableInterface;
use Elastica\Type as BaseType;
use Elastica\Util;

/**
 * Abstract helper class to implement search indices based on models.
 *
 * This abstract model should help creating search index and a subtype
 * with some easy config entries that are overloaded.
 *
 * The following variables have to be set:
 *    - $_indexName
 *    - $_typeName
 *
 * The following variables can be set for additional configuration
 *    - $_mapping: Value type mapping for the given type
 *    - $_indexParams: Parameters for the index
 *
 * @todo Add some settings examples to code
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractType implements SearchableInterface
{
    const MAX_DOCS_PER_REQUEST = 1000;

    /**
     * Index name.
     *
     * @var string Index name
     */
    protected $_indexName;

    /**
     * Index name.
     *
     * @var string Index name
     */
    protected $_typeName;

    /**
     * Client.
     *
     * @var \Elastica\Client Client object
     */
    protected $_client;

    /**
     * Index.
     *
     * @var \Elastica\Index Index object
     */
    protected $_index;

    /**
     * Type.
     *
     * @var \Elastica\Type Type object
     */
    protected $_type;

    /**
     * Mapping.
     *
     * @var array Mapping
     */
    protected $_mapping = [];

    /**
     * Index params.
     *
     * @var array Index  params
     */
    protected $_indexParams = [];

    /**
     * Source.
     *
     * @var bool Source
     */
    protected $_source = true;

    /**
     * Creates index object with client connection.
     *
     * Reads index and type name from protected vars _indexName and _typeName.
     * Has to be set in child class
     *
     * @param \Elastica\Client $client OPTIONAL Client object
     *
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct(Client $client = null)
    {
        if (!$client) {
            $client = new Client();
        }

        if (empty($this->_indexName)) {
            throw new InvalidException('Index name has to be set');
        }

        if (empty($this->_typeName)) {
            throw new InvalidException('Type name has to be set');
        }

        $this->_client = $client;
        $this->_index = new Index($this->_client, $this->_indexName);
        $this->_type = new BaseType($this->_index, $this->_typeName);
    }

    /**
     * Creates the index and sets the mapping for this type.
     *
     * @param bool $recreate OPTIONAL Recreates the index if true (default = false)
     */
    public function create($recreate = false)
    {
        $this->getIndex()->create($this->_indexParams, $recreate);

        $mapping = new Mapping($this->getType());
        $mapping->setProperties($this->_mapping);
        $mapping->setSource(['enabled' => $this->_source]);
        $mapping->send();
    }

    /**
     * @param string|\Elastica\Query $query
     * @param array|int              $options
     *
     * @return \Elastica\Search
     */
    public function createSearch($query = '', $options = null)
    {
        return $this->getType()->createSearch($query, $options);
    }

    /**
     * Search on the type.
     *
     * @param string|array|\Elastica\Query $query   Array with all query data inside or a Elastica\Query object
     * @param null                         $options
     *
     * @return \Elastica\ResultSet with all results inside
     *
     * @see \Elastica\SearchableInterface::search
     */
    public function search($query = '', $options = null)
    {
        return $this->getType()->search($query, $options = null);
    }

    /**
     * Count docs in the type based on query.
     *
     * @param string|array|\Elastica\Query $query Array with all query data inside or a Elastica\Query object
     *
     * @return int number of documents matching the query
     *
     * @see \Elastica\SearchableInterface::count
     */
    public function count($query = '')
    {
        return $this->getType()->count($query);
    }

    /**
     * Returns the search index.
     *
     * @return \Elastica\Index Index object
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * Returns type object.
     *
     * @return \Elastica\Type Type object
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Converts given time to format: 1995-12-31T23:59:59Z.
     *
     * This is the lucene date format
     *
     * @param int $date Date input (could be string etc.) -> must be supported by strtotime
     *
     * @return string Converted date string
     */
    public function convertDate($date)
    {
        return Util::convertDate($date);
    }
}
