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
    private $_results;

    /**
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
     * @return Result[]
     */
    public function getResults()
    {
        return $this->_results;
    }

    /**
     * Returns all Documents.
     *
     * @return Document[]
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
     */
    public function hasSuggests(): bool
    {
        $data = $this->_response->getData();

        return isset($data['suggest']);
    }

    /**
     * Return all suggests.
     *
     * @return array suggest results
     */
    public function getSuggests(): array
    {
        $data = $this->_response->getData();

        return $data['suggest'] ?? [];
    }

    /**
     * Returns whether aggregations exist.
     */
    public function hasAggregations(): bool
    {
        $data = $this->_response->getData();

        return isset($data['aggregations']);
    }

    /**
     * Returns all aggregation results.
     */
    public function getAggregations(): array
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
     */
    public function getAggregation(string $name): array
    {
        $data = $this->_response->getData();

        if (isset($data['aggregations'][$name])) {
            return $data['aggregations'][$name];
        }

        throw new InvalidException("This result set does not contain an aggregation named {$name}.");
    }

    /**
     * Returns the total number of found hits.
     */
    public function getTotalHits(): int
    {
        $data = $this->_response->getData();

        return (int) ($data['hits']['total']['value'] ?? 0);
    }

    /**
     * Returns the total number relation of found hits.
     */
    public function getTotalHitsRelation(): string
    {
        $data = $this->_response->getData();

        return $data['hits']['total']['relation'] ?? 'eq';
    }

    /**
     * Returns the max score of the results found.
     */
    public function getMaxScore(): float
    {
        $data = $this->_response->getData();

        return (float) ($data['hits']['max_score'] ?? 0);
    }

    /**
     * Returns the total number of ms for this search to complete.
     */
    public function getTotalTime(): int
    {
        $data = $this->_response->getData();

        return $data['took'] ?? 0;
    }

    /**
     * Returns true if the query has timed out.
     */
    public function hasTimedOut(): bool
    {
        $data = $this->_response->getData();

        return !empty($data['timed_out']);
    }

    /**
     * Returns response object.
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    public function getQuery(): Query
    {
        return $this->_query;
    }

    /**
     * Returns size of current set.
     */
    public function count(): int
    {
        return \count($this->_results);
    }

    /**
     * Returns size of current suggests.
     */
    public function countSuggests(): int
    {
        return \count($this->getSuggests());
    }

    /**
     * Returns the current object of the set.
     *
     * @return Result Set object
     */
    public function current(): Result
    {
        return $this->_results[$this->key()];
    }

    /**
     * Sets pointer (current) to the next item of the set.
     */
    public function next(): void
    {
        ++$this->_position;
    }

    /**
     * Returns the position of the current entry.
     *
     * @return int Current position
     */
    public function key(): int
    {
        return $this->_position;
    }

    /**
     * Check if an object exists at the current position.
     *
     * @return bool True if object exists
     */
    public function valid(): bool
    {
        return isset($this->_results[$this->key()]);
    }

    /**
     * Resets position to 0, restarts iterator.
     */
    public function rewind(): void
    {
        $this->_position = 0;
    }

    /**
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param int $offset
     */
    public function offsetExists($offset): bool
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
     */
    public function offsetGet($offset): Result
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
    public function offsetSet($offset, $value): void
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
    public function offsetUnset($offset): void
    {
        unset($this->_results[$offset]);
    }
}
