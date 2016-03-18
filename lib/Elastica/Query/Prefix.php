<?php

namespace Elastica\Query;

/**
 * Prefix query.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
 */
class Prefix extends AbstractQuery
{
    /**
     * Constructs the Prefix query object.
     *
     * @param array $prefix OPTIONAL Calls setRawPrefix with the given $prefix array
     */
    public function __construct(array $prefix = array())
    {
        $this->setRawPrefix($prefix);
    }

    /**
     * setRawPrefix can be used instead of setPrefix if some more special
     * values for a prefix have to be set.
     *
     * @param array $prefix Prefix array
     *
     * @return $this
     */
    public function setRawPrefix(array $prefix)
    {
        return $this->setParams($prefix);
    }

    /**
     * Adds a prefix to the prefix query.
     *
     * @param string       $key   Key to query
     * @param string|array $value Values(s) for the query. Boost can be set with array
     * @param float        $boost OPTIONAL Boost value (default = 1.0)
     *
     * @return $this
     */
    public function setPrefix($key, $value, $boost = 1.0)
    {
        return $this->setRawPrefix(array($key => array('value' => $value, 'boost' => $boost)));
    }
}
