<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanFirst query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-first-query.html
 */
class SpanFirst extends AbstractSpanQuery
{
    /**
     * Constructs a SpanFirst query object.
     *
     * @param AbstractQuery|array $match
     * @param int                 $end
     */
    public function __construct($match = null, ?int $end = null)
    {
        if (null !== $match) {
            $this->setMatch($match);
        }

        if (null !== $match) {
            $this->setEnd($end);
        }
    }

    /**
     * Set the query to be wrapped into the span multi query.
     *
     * @param AbstractSpanQuery|array $args Matching query
     *
     * @throws InvalidException If not valid query
     *
     * @return $this
     */
    public function setMatch($args): self
    {
        return $this->_setQuery('match', $args);
    }

    /**
     * Set the maximum end position for the match query.
     *
     * @return $this
     */
    public function setEnd(int $end): self
    {
        $this->setParam('end', $end);

        return $this;
    }

    /**
     * Sets a query to the current object.
     *
     * @param string              $type Query type
     * @param AbstractQuery|array $args Query
     *
     * @throws InvalidException If not valid query
     *
     * @return $this
     */
    protected function _setQuery(string $type, $args): self
    {
        if (!\is_array($args) && !($args instanceof AbstractSpanQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractSpanQuery');
        }

        return $this->setParam($type, $args);
    }
}
