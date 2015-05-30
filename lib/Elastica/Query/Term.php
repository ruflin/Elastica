<?php
namespace Elastica\Query;

/**
 * Term query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
 */
class Term extends AbstractQuery
{
    /**
     * Constructs the Term query object.
     *
     * @param array $term OPTIONAL Calls setTerm with the given $term array
     */
    public function __construct(array $term = array())
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
        return $this->setRawTerm(array($key => array('value' => $value, 'boost' => $boost)));
    }
}
