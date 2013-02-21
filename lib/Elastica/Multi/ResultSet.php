<?php

namespace Elastica\Multi;
use Elastica\Exception\InvalidException;
use Elastica\Response;
use Elastica\Search as BaseSearch;
use Elastica\ResultSet as BaseResultSet;

/**
 * Elastica multi search result set
 * List of result sets for each search request
 *
 * @category Xodoa
 * @package Elastica
 * @author munkie
 */
class ResultSet implements \IteratorAggregate, \Countable
{
    /**
     * Result Sets
     *
     * @var array|\Elastica\ResultSet[] Result Sets
     */
    protected $_resultSets = array();

    /**
     * Response
     *
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Constructs ResultSet object
     *
     * @param \Elastica\Response       $response
     * @param array|\Elastica\Search[] $searches
     */
    public function __construct(Response $response, array $searches)
    {
        $this->_init($response, $searches);
    }

    /**
     * @param  \Elastica\Response                   $response
     * @param  array|\Elastica\Search[]             $searches
     * @throws \Elastica\Exception\InvalidException
     */
    protected function _init(Response $response, array $searches)
    {
        $this->_response = $response;
        $responseData = $response->getData();

        if (isset($responseData['responses']) && is_array($responseData['responses'])) {
            foreach ($responseData['responses'] as $key => $responseData) {

                if (!isset($searches[$key])) {
                    throw new InvalidException('No result found for search #' . $key);
                } elseif (!$searches[$key] instanceof BaseSearch) {
                    throw new InvalidException('Invalid object for search #' . $key . ' provided. Should be Elastica\Search');
                }

                $search = $searches[$key];
                $query = $search->getQuery();

                $response = new Response($responseData);
                $this->_resultSets[] = new BaseResultSet($response, $query);
            }
        }
    }

    /**
     * @return array|\Elastica\ResultSet[]
     */
    public function getResultSets()
    {
        return $this->_resultSets;
    }

    /**
     * Returns response object
     *
     * @return \Elastica\Response Response object
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
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_resultSets);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_resultSets);
    }
}
