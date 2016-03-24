<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Elastica result set.
 *
 * List of all hits that are returned for a search on elasticsearch
 * Result set implements iterator
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ResultSet implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * Results.
     *
     * @var Result[] Results
     */
    private $_results = array();

    /**
     * Current position.
     *
     * @var int Current position
     */
    private $_position = 0;

    /**
     * Response.
     *
     * @var \Elastica\Response Response object
     */
    private $_response;

    /**
     * Query.
     *
     * @var \Elastica\Query Query object
     */
    private $_query;

    /**
     * Constructs ResultSet object.
     *
     * @param Response $response Response object
     * @param Query $query Query object
     * @param Result[] $results
     */
    public function __construct(Response $response, Query $query, $results)
    {
        $this->_query = $query;
        $this->_response = $response;
        $this->_results = $results;
    }

    /**
     * Returns all results.
     *
     * @return Result[] Results
     */
    public function getResults()
    {
        return $this->_results;
    }

    /**
     * Returns all Documents.
     *
     * @return array Documents \Elastica\Document
     */
    public function getDocuments()
    {
        $documents = array();
        foreach ($this->_results as $doc) {
            $documents[] = $doc->getDocument();
        }

        return $documents;
    }

    /**
     * Returns true if the response contains suggestion results; false otherwise.
     *
     * @return bool
     */
    public function hasSuggests()
    {
        $data = $this->_response->getData();

        return isset($data['suggest']);
    }

    /**
     * Return all suggests.
     *
     * @return array suggest results
     */
    public function getSuggests()
    {
        $data = $this->_response->getData();

        return isset($data['suggest']) ? $data['suggest'] : array();
    }

    /**
     * Returns whether aggregations exist.
     *
     * @return bool Aggregation existence
     */
    public function hasAggregations()
    {
        $data = $this->_response->getData();

        return isset($data['aggregations']);
    }

    /**
     * Returns all aggregation results.
     *
     * @return array
     */
    public function getAggregations()
    {
        $data = $this->_response->getData();

        return isset($data['aggregations']) ? $data['aggregations'] : array();
    }

    /**
     * Retrieve a specific aggregation from this result set.
     *
     * @param string $name the name of the desired aggregation
     *
     * @throws Exception\InvalidException if an aggregation by the given name cannot be found
     *
     * @return array
     */
    public function getAggregation($name)
    {
        $data = $this->_response->getData();

        if (isset($data['aggregations']) && isset($data['aggregations'][$name])) {
            return $data['aggregations'][$name];
        }
        throw new InvalidException("This result set does not contain an aggregation named {$name}.");
    }

    /**
     * Returns the total number of found hits.
     *
     * @return int Total hits
     */
    public function getTotalHits()
    {
        return isset($result['hits']['total']) ? (int) $result['hits']['total'] : 0;
    }

    /**
     * Returns the max score of the results found.
     *
     * @return float Max Score
     */
    public function getMaxScore()
    {
        return isset($result['hits']['max_score']) ? (float) $result['hits']['max_score'] : 0;
    }

    /**
     * Returns the total number of ms for this search to complete.
     *
     * @return int Total time
     */
    public function getTotalTime()
    {
        return isset($result['took']) ? $result['took'] : 0;
    }

    /**
     * Returns true if the query has timed out.
     *
     * @return bool Timed out
     */
    public function hasTimedOut()
    {
        return !empty($result['timed_out']);
    }

    /**
     * Returns response object.
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Elastica\Query
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Returns size of current set.
     *
     * @return int Size of set
     */
    public function count()
    {
        return sizeof($this->_results);
    }

    /**
     * Returns size of current suggests.
     *
     * @return int Size of suggests
     */
    public function countSuggests()
    {
        return sizeof($this->getSuggests());
    }

    /**
     * Returns the current object of the set.
     *
     * @return \Elastica\Result|bool Set object or false if not valid (no more entries)
     */
    public function current()
    {
        if ($this->valid()) {
            return $this->_results[$this->key()];
        } else {
            return false;
        }
    }

    /**
     * Sets pointer (current) to the next item of the set.
     */
    public function next()
    {
        ++$this->_position;

        return $this->current();
    }

    /**
     * Returns the position of the current entry.
     *
     * @return int Current position
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Check if an object exists at the current position.
     *
     * @return bool True if object exists
     */
    public function valid()
    {
        return isset($this->_results[$this->key()]);
    }

    /**
     * Resets position to 0, restarts iterator.
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param int $offset
     *
     * @return bool true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->_results[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param int $offset
     *
     * @throws Exception\InvalidException If offset doesn't exist
     *
     * @return Result|null
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->_results[$offset];
        } else {
            throw new InvalidException('Offset does not exist.');
        }
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int    $offset
     * @param Result $value
     *
     * @throws Exception\InvalidException
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Result)) {
            throw new InvalidException('ResultSet is a collection of Result only.');
        }

        if (!isset($this->_results[$offset])) {
            throw new InvalidException('Offset does not exist.');
        }

        $this->_results[$offset] = $value;
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_results[$offset]);
    }
}
