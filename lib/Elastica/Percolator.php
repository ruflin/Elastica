<?php

namespace Elastica;

/**
 * Percolator class
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/percolate/
 */
class Percolator
{
    /**
     * Index object
     *
     * @var \Elastica\Index
     */
    protected $_index = null;

    /**
     * Construct new percolator
     *
     * @param \Elastica\Index $index
     */
    public function __construct(Index $index)
    {
        $this->_index = $index;
    }

    /**
     * Registers a percolator query, with optional extra fields to include in the registered query.
     *
     * @param  string                                               $name   Query name
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query  Query to add
     * @param  array                                                $fields Extra fields to include in the registered query
     *                                                                      and can be used to filter executed queries. 
     * @return \Elastica\Response
     */
    public function registerQuery($name, $query, $fields = array())
    {
        $path = '_percolator/' . $this->_index->getName() . '/' . $name;
        $query = Query::create($query);
        
        $data = array_merge($query->toArray(), $fields);

        return $this->_index->getClient()->request($path, Request::PUT, $data);
    }

    /**
     * Removes a percolator query
     * @param  string            $name query name
     * @return \Elastica\Response
     */
    public function unregisterQuery($name)
    {
        $path = '_percolator/' . $this->_index->getName() . '/' . $name;

        return $this->_index->getClient()->request($path, Request::DELETE);
    }

    /**
     * Match a document to percolator queries
     *
     * @param  \Elastica\Document                                   $doc
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query Query to filter the percolator queries which
     *                                                                     are executed.
     * @param  string                                               $type
     * @return array With matching registered queries.
     */
    public function matchDoc(Document $doc, $query = null, $type = 'type')
    {
        $path = $this->_index->getName() . '/' . $type . '/_percolate';
        $data = array('doc' => $doc->getData());

        // Add query to filter the percolator queries which are executed.
        if ($query) {
            $query = Query::create($query);
            $data['query'] = $query->getQuery();
        }

        $response = $this->getIndex()->getClient()->request($path, Request::GET, $data);
        $data = $response->getData();

        return $data['matches'];
    }

    /**
     * Return index object
     *
     * @return \Elastica\Index
     */
    public function getIndex()
    {
        return $this->_index;
    }
}
