<?php
namespace Elastica;

/**
 * Percolator class.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-percolate.html
 */
class Percolator
{
    const EXTRA_FILTER = 'filter';
    const EXTRA_QUERY = 'query';
    const EXTRA_SIZE = 'size';
    const EXTRA_TRACK_SCORES = 'track_scores';
    const EXTRA_SORT = 'sort';
    const EXTRA_AGGS = 'aggs';
    const EXTRA_HIGHLIGHT = 'highlight';

    private $_extraRequestBodyOptions = array(
        self::EXTRA_FILTER,
        self::EXTRA_QUERY,
        self::EXTRA_SIZE,
        self::EXTRA_TRACK_SCORES,
        self::EXTRA_SORT,
        self::EXTRA_AGGS,
        self::EXTRA_HIGHLIGHT,
    );

    /**
     * Index object.
     *
     * @var \Elastica\Index
     */
    protected $_index = null;

    /**
     * Construct new percolator.
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
     * @param string                                               $name   Query name
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query  Query to add
     * @param array                                                $fields Extra fields to include in the registered query
     *                                                                     and can be used to filter executed queries.
     *
     * @return \Elastica\Response
     */
    public function registerQuery($name, $query, $fields = array())
    {
        $path = $this->_index->getName().'/.percolator/'.$name;
        $query = Query::create($query);

        $data = array_merge($query->toArray(), $fields);

        return $this->_index->getClient()->request($path, Request::PUT, $data);
    }

    /**
     * Removes a percolator query.
     *
     * @param string $name query name
     *
     * @return \Elastica\Response
     */
    public function unregisterQuery($name)
    {
        $path = $this->_index->getName().'/.percolator/'.$name;

        return $this->_index->getClient()->request($path, Request::DELETE);
    }

    /**
     * Match a document to percolator queries.
     *
     * @param \Elastica\Document                                   $doc
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query  Query to filter the percolator queries which
     *                                                                     are executed.
     * @param string                                               $type
     * @param array                                                $params Supports setting additional request body options to the percolate request.
     *                                                                     [ Percolator::EXTRA_FILTER,
     *                                                                     Percolator::EXTRA_QUERY,
     *                                                                     Percolator::EXTRA_SIZE,
     *                                                                     Percolator::EXTRA_TRACK_SCORES,
     *                                                                     Percolator::EXTRA_SORT,
     *                                                                     Percolator::EXTRA_AGGS,
     *                                                                     Percolator::EXTRA_HIGHLIGHT ]
     *
     * @return array With matching registered queries.
     */
    public function matchDoc(Document $doc, $query = null, $type = 'type', $params = array())
    {
        $path = $this->_index->getName().'/'.$type.'/_percolate';
        $data = array('doc' => $doc->getData());

        $this->_applyAdditionalRequestBodyOptions($params, $data);

        return $this->_percolate($path, $query, $data, $params);
    }

    /**
     * Percolating an existing document.
     *
     * @param string                                               $id
     * @param string                                               $type
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query  Query to filter the percolator queries which
     *                                                                     are executed.
     * @param array                                                $params Supports setting additional request body options to the percolate request.
     *                                                                     [ Percolator::EXTRA_FILTER,
     *                                                                     Percolator::EXTRA_QUERY,
     *                                                                     Percolator::EXTRA_SIZE,
     *                                                                     Percolator::EXTRA_TRACK_SCORES,
     *                                                                     Percolator::EXTRA_SORT,
     *                                                                     Percolator::EXTRA_AGGS,
     *                                                                     Percolator::EXTRA_HIGHLIGHT ]
     *
     * @return array With matching registered queries.
     */
    public function matchExistingDoc($id, $type, $query = null, $params = array())
    {
        $id = urlencode($id);
        $path = $this->_index->getName().'/'.$type.'/'.$id.'/_percolate';

        $data = array();
        $this->_applyAdditionalRequestBodyOptions($params, $data);

        return $this->_percolate($path, $query, $data, $params);
    }

    /**
     * Process the provided parameters and apply them to the data array.
     *
     * @param &$params
     * @param &$data
     */
    protected function _applyAdditionalRequestBodyOptions(&$params, &$data)
    {
        foreach ($params as $key => $value) {
            if (in_array($key, $this->_extraRequestBodyOptions)) {
                $data[$key] = $params[$key];
                unset($params[$key]);
            }
        }
    }

    /**
     * @param string                                               $path
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query] $query  [description]
     * @param array                                                $data
     * @param array                                                $params
     *
     * @return array
     */
    protected function _percolate($path, $query, $data = array(), $params = array())
    {
        // Add query to filter the percolator queries which are executed.
        if ($query) {
            $query = Query::create($query);
            $data['query'] = $query->getQuery()->toArray();
        }

        $response = $this->getIndex()->getClient()->request($path, Request::GET, $data, $params);
        $data = $response->getData();

        if (isset($data['matches'])) {
            return $data['matches'];
        }

        return array();
    }

    /**
     * Return index object.
     *
     * @return \Elastica\Index
     */
    public function getIndex()
    {
        return $this->_index;
    }
}
