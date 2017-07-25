<?php
namespace Elastica\Query;

/**
 * SpanTerm query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 * @author Marek Hernik <marek.hernik@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-term-query.html
 */
class SpanTerm extends AbstractSpanQuery
{
    /**
     * Constructs the SpanTerm query object.
     *
     * @param array $term OPTIONAL Calls setRawTerm with the given $term array
     */
    public function __construct(array $term = [])
    {
        $this->setRawTerm($term);
    }

    /**
     * Set term can be used instead of setTerm if some more special
     * values for a term have to be set.
     *
     * @param array $term Term array
     *
     * @return $this
     */
    public function setRawTerm(array $term)
    {
        return $this->setParams($term);
    }

    /**
     * Adds a term to the term query.
     *
     * @param string       $key   Key to query
     * @param string|array $value Values(s) for the query. Boost can be set with array
     * @param float        $boost OPTIONAL Boost value (default = 1.0)
     *
     * @return $this
     */
    public function setTerm($key, $value, $boost = 1.0)
    {
        return $this->setRawTerm([$key => ['value' => $value, 'boost' => $boost]]);
    }
}
