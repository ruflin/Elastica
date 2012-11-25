<?php
/**
 * Abstract helper class to implement search indices based on models.
 *
 * This abstract model should help creating search index and a subtype
 * with some easy config entries that are overloaded.
 *
 * The following variables have to be set:
 *	- $_indexName
 *	- $_typeName
 *
 * The following variables can be set for additional configuration
 *	- $_mapping: Value type mapping for the given type
 *	- $_indexParams: Parameters for the index
 *
 * @todo Add some settings examples to code
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Type_Abstract implements Elastica_Searchable
{
    const MAX_DOCS_PER_REQUEST = 1000;

    /**
     * Index name
     *
     * @var string Index name
     */
    protected $_indexName = '';

    /**
     * Index name
     *
     * @var string Index name
     */
    protected $_typeName = '';

    /**
     * Client
     *
     * @var Elastica_Client Client object
     */
    protected $_client = null;

    /**
     * Index
     *
     * @var Elastica_Index Index object
     */
    protected $_index = null;

    /**
     * Type
     *
     * @var Elastica_Type Type object
     */
    protected $_type = null;

    /**
     * Mapping
     *
     * @var array Mapping
     */
    protected $_mapping = array();

    /**
     * Index params
     *
     * @var array Index  params
     */
    protected $_indexParams = array();

    /**
     * Source
     *
     * @var boolean Source
     */
    protected $_source = true;

    /**
     * Creates index object with client connection
     *
     * Reads index and type name from protected vars _indexName and _typeName.
     * Has to be set in child class
     *
     * @param Elastica_Client $client OPTIONAL Client object
     * @throws Elastica_Exception_Invalid
     */
    public function __construct(Elastica_Client $client = null)
    {
        if (!$client) {
            $client = new Elastica_Client();
        }

        if (empty($this->_indexName)) {
            throw new Elastica_Exception_Invalid('Index name has to be set');
        }

        if (empty($this->_typeName)) {
            throw new Elastica_Exception_Invalid('Type name has to be set');
        }

        $this->_client = $client;
        $this->_index = new Elastica_Index($this->_client, $this->_indexName);
        $this->_type = new Elastica_Type($this->_index, $this->_typeName);
    }

    /**
     * Creates the index and sets the mapping for this type
     *
     * @param bool $recreate OPTIONAL Recreates the index if true (default = false)
     */
    public function create($recreate = false)
    {
        $this->getIndex()->create($this->_indexParams, $recreate);

        $mapping = new Elastica_Type_Mapping($this->getType());
        $mapping->setProperties($this->_mapping);
        $mapping->setSource(array('enabled' => $this->_source));
        $mapping->send();
    }

    /**
     * Search on the type
     *
     * @param  string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
     * @return Elastica_ResultSet          ResultSet with all results inside
     * @see Elastica_Searchable::search
     */
    public function search($query)
    {
        return $this->getType()->search($query);
    }

    /**
     * Count docs in the type based on query
     *
     * @param  string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
     * @return int                         number of documents matching the query
     * @see Elastica_Searchable::count
     */
    public function count($query = '')
    {
        return $this->getType()->count($query);
    }

    /**
     * Returns the search index
     *
     * @return Elastica_Index Index object
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * Returns type object
     *
     * @return Elastica_Type Type object
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Converts given time to format: 1995-12-31T23:59:59Z
     *
     * This is the lucene date format
     *
     * @param  int    $date Date input (could be string etc.) -> must be supported by strtotime
     * @return string Converted date string
     */
    public function convertDate($date)
    {
        return Elastica_Util::convertDate($date);
    }
}
