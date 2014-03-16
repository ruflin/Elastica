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
     * @param  string $name Query name
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query Query to add
     * @param  array $fields Extra fields to include in the registered query
     *                                                                      and can be used to filter executed queries.
     * @return \Elastica\Response
     */
    public function registerQuery($name, $query, $fields = array())
    {
        $path = $this->_index->getName() . '/.percolator/' . $name;
        $query = Query::create($query);

        $data = array_merge($query->toArray(), $fields);

        return $this->_index->getClient()->request($path, Request::PUT, $data);
    }

    /**
     * Removes a percolator query
     * @param  string $name query name
     * @return \Elastica\Response
     */
    public function unregisterQuery($name)
    {
        $path = $this->_index->getName() . '/.percolator/'  . $name;

        return $this->_index->getClient()->request($path, Request::DELETE);
    }

    /**
     * Match a document to percolator queries
     *
     * @param  \Elastica\Document $doc
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query Query to filter the percolator queries which
     *                                                                     are executed.
     * @param  string $type
     * @param  array  $params
     * @return array With matching registered queries.
     */
    public function matchDoc(Document $doc, $query = null, $type = 'type', $params = array())
    {
        $path = $this->_index->getName() . '/' . $type . '/_percolate';
        $data = array('doc' => $doc->getData());

        return $this->_percolate($path, $query, $data, $params);
    }

    /**
     * Percolating an existing document
     *
     * @param  string $id
     * @param  string $type
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query Query to filter the percolator queries which
     *                                                                     are executed.
     * @param  array  $params
     * @return array With matching registered queries.
     */
    public function matchExistingDoc($id, $type, $query = null, $params = array())
    {
        $id = urlencode($id);
        $path = $this->_index->getName() . '/' . $type . '/'. $id . '/_percolate';

        return $this->_percolate($path, $query, array(), $params);
    }

    /**
     * @param  string $path
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query] $query  [description]
     * @param  array  $data
     * @param  array  $params
     * @return array
     */
    protected function _percolate($path, $query, $data = array(), $params = array())
    {
        // Add query to filter the percolator queries which are executed.
        if ($query) {
            $query = Query::create($query);
            $data['query'] = $query->getQuery();
        }

        $response = $this->getIndex()->getClient()->request($path, Request::GET, $data, $params);
        $data = $response->getData();

        if (isset($data['matches'])) {
            return $data['matches'];
        }
        return array();
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
