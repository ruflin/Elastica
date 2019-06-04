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
     * Current position.
     *
     * @var int Current position
     */
    private $_position = 0;

    /**
     * Query.
     *
     * @var Query Query object
     */
    private $_query;

    /**
     * Response.
     *
     * @var Response Response object
     */
    private $_response;

    /**
     * Results.
     *
     * @var Result[] Results
     */
    private $_results = [];

    /**
     * Constructs ResultSet object.
     *
     * @param Response $response Response object
     * @param Query    $query    Query object
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
        $documents = [];
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

        return $data['suggest'] ?? [];
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

        return $data['aggregations'] ?? [];
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
        $data = $this->_response->getData();

        return (int) ($data['hits']['total']['value'] ?? 0);
    }

    /**
     * Returns the max score of the results found.
     *
     * @return float Max Score
     */
    public function getMaxScore()
    {
        $data = $this->_response->getData();

        return (float) ($data['hits']['max_score'] ?? 0);
    }

    /**
     * Returns the total number of ms for this search to complete.
     *
     * @return int Total time
     */
    public function getTotalTime()
    {
        $data = $this->_response->getData();

        return $data['took'] ?? 0;
    }

    /**
     * Returns true if the query has timed out.
     *
     * @return bool Timed out
     */
    public function hasTimedOut()
    {
        $data = $this->_response->getData();

        return !empty($data['timed_out']);
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return Query
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
        return \count($this->_results);
    }

    /**
     * Returns size of current suggests.
     *
     * @return int Size of suggests
     */
    public function countSuggests()
    {
        return \sizeof($this->getSuggests());
    }

    /**
     * Returns the current object of the set.
     *
     * @return \Elastica\Result Set object
     */
    public function current()
    {
        return $this->_results[$this->key()];
    }

    /**
     * Sets pointer (current) to the next item of the set.
     */
    public function next()
    {
        ++$this->_position;
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
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param int $offset
     *
     * @return bool true on success or false on failure
     */
    public function offsetExists($offset)
    {
        return isset($this->_results[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param int $offset
     *
     * @throws Exception\InvalidException If offset doesn't exist
     *
     * @return Result
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->_results[$offset];
        }

        throw new InvalidException('Offset does not exist.');
    }

    /**
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
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
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_results[$offset]);
    }
}
