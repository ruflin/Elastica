<?php

namespace Elastica\Query;

/**
 * Simple query
 * Pure php array query. Can be used to create any not existing type of query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Simple extends AbstractQuery
{
    /**
     * Query.
     *
     * @var array Query
     */
    protected $_query = array();

    /**
     * Constructs a query based on an array.
     *
     * @param array $query Query array
     */
    public function __construct(array $query)
    {
        $this->setQuery($query);
    }

    /**
     * Sets new query array.
     *
     * @param array $query Query array
     *
     * @return $this
     */
    public function setQuery(array $query)
    {
        $this->_query = $query;

        return $this;
    }

    /**
     * Converts query to array.
     *
     * @return array Query array
     *
     * @see \Elastica\Query\AbstractQuery::toArray()
     */
    public function toArray()
    {
        return $this->_query;
    }
}
