<?php
/**
 * Percolator class
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/percolate.html
 */
class Elastica_Percolator
{
    /**
     * Index object
     *
     * @var Elastica_Index
     */
    protected $_index = null;

    /**
     * Construct new perlocator
     *
     * @param Elastica_Index $index
     */
    public function __construct(Elastica_Index $index)
    {
        $this->_index = $index;
    }

    /**
     * Registers a percolator query
     *
     * @param  string                                        $name  Query name
     * @param  string|Elastica_Query|Elastica_Query_Abstract $query Query to add
     * @return Elastica_Response
     */
    public function registerQuery($name, $query)
    {
        $path = '_percolator/' . $this->_index->getName() . '/' . $name;
        $query = Elastica_Query::create($query);

        return $this->_index->getClient()->request($path, Elastica_Request::PUT, $query->toArray());
    }

    /**
     * Removes a percolator query
     * @param  string            $name query name
     * @return Elastica_Response
     */
    public function unregisterQuery($name)
    {
        $path = '_percolator/' . $this->_index->getName() . '/' . $name;

        return $this->_index->getClient()->request($path, Elastica_Request::DELETE);
    }

    /**
     * Match a document to percolator queries
     *
     * @param  Elastica_Document                             $doc
     * @param  string|Elastica_Query|Elastica_Query_Abstract $query Not implemented yet
     * @return Elastica_Response
     */
    public function matchDoc(Elastica_Document $doc, $query = null)
    {
        $path = $this->_index->getName() . '/type/_percolate';
        $data = array('doc' => $doc->getData());

        $response = $this->getIndex()->getClient()->request($path, Elastica_Request::GET, $data);
        $data = $response->getData();

        return $data['matches'];
    }

    /**
     * Return index object
     *
     * @return Elastica_Index
     */
    public function getIndex()
    {
        return $this->_index;
    }
}
