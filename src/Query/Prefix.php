<?php

namespace Elastica\Query;

/**
 * Prefix query.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
 */
class Prefix extends AbstractQuery
{
    /**
     * @param array $prefix OPTIONAL Calls setRawPrefix with the given $prefix array
     */
    public function __construct(array $prefix = [])
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
    public function setRawPrefix(array $prefix): self
    {
        return $this->setParams($prefix);
    }

    /**
     * Adds a prefix to the prefix query.
     *
     * @param string       $key   Key to query
     * @param array|string $value Values(s) for the query. Boost can be set with array
     * @param float        $boost OPTIONAL Boost value (default = 1.0)
     *
     * @return $this
     */
    public function setPrefix(string $key, $value, float $boost = 1.0): self
    {
        return $this->setRawPrefix([$key => ['value' => $value, 'boost' => $boost]]);
    }
}
