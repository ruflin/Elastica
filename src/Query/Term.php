<?php

namespace Elastica\Query;

/**
 * Term query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
 */
class Term extends AbstractQuery
{
    /**
     * Constructs the Term query object.
     *
     * @param array $term Calls setTerm with the given $term array
     */
    public function __construct(array $term = [])
    {
        $this->setRawTerm($term);
    }

    /**
     * Set term can be used instead of addTerm if some more special
     * values for a term have to be set.
     *
     * @param array $term Term array
     *
     * @return $this
     */
    public function setRawTerm(array $term): self
    {
        return $this->setParams($term);
    }

    /**
     * Adds a term to the term query.
     *
     * @param string       $key   Key to query
     * @param array|string $value Values(s) for the query. Boost can be set with array
     * @param float        $boost OPTIONAL Boost value (default = 1.0)
     *
     * @return $this
     */
    public function setTerm(string $key, $value, float $boost = 1.0): self
    {
        return $this->setRawTerm([$key => ['value' => $value, 'boost' => $boost]]);
    }
}
