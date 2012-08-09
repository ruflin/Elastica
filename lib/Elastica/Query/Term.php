<?php
/**
 * Term query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/term-query.html
 */
class Elastica_Query_Term extends Elastica_Query_Abstract
{
    /**
     * Constructs the Term query object
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
     * @param  array               $term Term array
     * @return Elastica_Query_Term Current object
     */
    public function setRawTerm(array $term)
    {
        return $this->setParams($term);
    }

    /**
     * Adds a term to the term query
     *
     * @param  string              $key   Key to query
     * @param  string|array        $value Values(s) for the query. Boost can be set with array
     * @param  float               $boost OPTIONAL Boost value (default = 1.0)
     * @return Elastica_Query_Term Current object
     */
    public function setTerm($key, $value, $boost = 1.0)
    {
        return $this->setRawTerm(array($key => array('value' => $value, 'boost' => $boost)));
    }
}
