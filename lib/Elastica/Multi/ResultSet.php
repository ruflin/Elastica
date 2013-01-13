<?php
/**
 * Elastica multi search result set
 * List of result sets for each search request
 *
 * @category Xodoa
 * @package Elastica
 * @author munkie
 */
class Elastica_Multi_ResultSet implements Iterator, Countable
{
    /**
     * Result Sets
     *
     * @var array|Elastica_ResultSet[] Result Sets
     */
    protected $_resultSets = array();

    /**
     * Current position
     *
     * @var int Current position
     */
    protected $_position = 0;

    /**
     * Response
     *
     * @var Elastica_Response Response object
     */
    protected $_response;

    /**
     * Constructs ResultSet object
     *
     * @param Elastica_Response $response
     * @param array|Elastica_Search[] $searches
     */
    public function __construct(Elastica_Response $response, array $searches)
    {
        $this->rewind();
        $this->_init($response, $searches);
    }

    /**
     * @param Elastica_Response $response
     * @param array|Elastica_Search[] $searches
     * @throws Elastica_Exception_Invalid
     */
    protected function _init(Elastica_Response $response, array $searches)
    {
        $this->_response = $response;
        $responseData = $response->getData();

        if (isset($responseData['responses']) && is_array($responseData['responses'])) {
            foreach ($responseData['responses'] as $key => $responseData) {

                if (!isset($searches[$key])) {
                    throw new Elastica_Exception_Invalid('No result found for search #' . $key);
                } elseif (!$searches[$key] instanceof Elastica_Search) {
                    throw new Elastica_Exception_Invalid('Invalid object for search #' . $key . ' provided. Should be Elastica_Search');
                }

                $search = $searches[$key];
                $query = $search->getQuery();

                $response = new Elastica_Response($responseData);
                $this->_resultSets[] = new Elastica_ResultSet($response, $query);
            }
        }
    }

    /**
     * @return array|Elastica_ResultSet[]
     */
    public function getResultSets()
    {
        return $this->_resultSets;
    }

    /**
     * Returns response object
     *
     * @return Elastica_Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * There is at least one result set with error
     *
     * @return bool
     */
    public function hasError()
    {
        foreach ($this->getResultSets() as $resultSet) {
            if ($resultSet->getResponse()->hasError()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool|Elastica_ResultSet
     */
    public function current()
    {
        if ($this->valid()) {
            return $this->_resultSets[$this->key()];
        } else {
            return false;
        }
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->_resultSets[$this->key()]);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_resultSets);
    }
}
