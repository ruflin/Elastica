<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanFirst query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-first-query.html
 */
class SpanFirst extends AbstractSpanQuery
{
    /**
     * Constructs a SpanFirst query object.
     *
     * @param \Elastica\Query\AbstractQuery|array $match OPTIONAL
     * @param int                                 $end   OPTIONAL
     */
    public function __construct($match = null, $end = null)
    {
        if (!is_null($match)) {
            $this->setMatch($match);
        }

        if (!is_null($match)) {
            $this->setEnd($end);
        }
    }

    /**
     * Set the query to be wrapped into the span multi query.
     *
     * @param \Elastica\Query\AbstractSpanQuery|array $args Matching query
     *
     * @throws \Elastica\Exception\InvalidException If not valid query
     *
     * @return $this
     */
    public function setMatch($args)
    {
        return $this->_setQuery('match', $args);
    }

    /**
     * Set the maximum end position for the match query.
     *
     * @param int $end
     *
     * @return $this
     */
    public function setEnd($end)
    {
        $this->setParam('end', $end);

        return $this;
    }

    /**
     * Sets a query to the current object.
     *
     * @param string                              $type Query type
     * @param \Elastica\Query\AbstractQuery|array $args Query
     *
     * @throws \Elastica\Exception\InvalidException If not valid query
     *
     * @return $this
     */
    protected function _setQuery($type, $args)
    {
        if (!is_array($args) && !($args instanceof AbstractSpanQuery)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\AbstractSpanQuery');
        }

        return $this->setParam($type, $args);
    }
}
