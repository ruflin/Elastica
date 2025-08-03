<?php

declare(strict_types=1);

namespace Elastica\Multi;

use Elastica\Response;
use Elastica\ResultSet as BaseResultSet;

/**
 * Elastica multi search result set
 * List of result sets for each search request.
 *
 * @author munkie
 */
class ResultSet implements \Iterator, \ArrayAccess, \Countable
{
    /**
     * Result Sets.
     *
     * @var array|BaseResultSet[] Result Sets
     */
    protected array $_resultSets;

    /**
     * Current position.
     *
     * @var int Current position
     */
    protected $_position = 0;

    protected Response $_response;

    /**
     * @param BaseResultSet[] $resultSets
     */
    public function __construct(Response $response, array $resultSets)
    {
        $this->_response = $response;
        $this->_resultSets = $resultSets;
    }

    /**
     * @return BaseResultSet[]
     */
    public function getResultSets(): array
    {
        return $this->_resultSets;
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    /**
     * There is at least one result set with error.
     */
    public function hasError(): bool
    {
        foreach ($this->getResultSets() as $resultSet) {
            if ($resultSet->getResponse()->hasError()) {
                return true;
            }
        }

        return false;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_resultSets[$this->key()];
    }

    public function next(): void
    {
        ++$this->_position;
    }

    public function key(): int
    {
        return $this->_position;
    }

    public function valid(): bool
    {
        return isset($this->_resultSets[$this->key()]);
    }

    public function rewind(): void
    {
        $this->_position = 0;
    }

    public function count(): int
    {
        return \count($this->_resultSets);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->_resultSets[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->_resultSets[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->_resultSets[] = $value;
        } else {
            $this->_resultSets[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->_resultSets[$offset]);
    }
}
