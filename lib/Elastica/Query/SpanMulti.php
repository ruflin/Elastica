<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanMulti query.
 *
 * @author Marek Hernik <marek.hernik@gmail.com>
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-multi-term-query.html
 */
class SpanMulti extends AbstractSpanQuery
{
    /**
     * Constructs a SpanMulti query object.
     *
     * @param AbstractQuery|array $match
     */
    public function __construct($match = null)
    {
        if (null !== $match) {
            $this->setMatch($match);
        }
    }

    /**
     * Set the query to be wrapped into the span multi query.
     *
     * @param AbstractQuery|array $args Matching query
     *
     * @return $this
     */
    public function setMatch($args): self
    {
        return $this->_setQuery('match', $args);
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
        if (!\is_array($args) && !($args instanceof AbstractQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractQuery');
        }

        return $this->setParam($type, $args);
    }
}
