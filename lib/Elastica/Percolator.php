<?php

namespace Elastica;

/**
 * Percolator class
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/percolate.html
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
     * Registers a percolator query
     *
     * @param  string                                             $name  Query name
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query Query to add
     * @return \Elastica\Response
     */
    public function registerQuery($name, $query)
    {
        $path = '_percolator/' . $this->_index->getName() . '/' . $name;
        $query = Query::create($query);

        return $this->_index->getClient()->request($path, Request::PUT, $query->toArray());
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
     * @param  \Elastica\Document                                  $doc
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query Query to filter the data
     * @return \Elastica\Response
     */
    public function matchDoc(Document $doc, $query = null)
    {
        $path = $this->_index->getName() . '/type/_percolate';
        $data = array('doc' => $doc->getData());

        // Add query to filter results after percolation
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
