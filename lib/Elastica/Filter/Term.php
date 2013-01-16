<?php

namespace Elastica\Filter;

/**
 * Term query
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/term-filter.html
 */
class Term extends AbstractFilter
{
    /**
     * Construct term filter
     *
     * @param array $term Term array
     */
    public function __construct(array $term = array())
    {
        $this->setRawTerm($term);
    }

    /**
     * Sets/overwrites key and term directly
     *
     * @param  array                      $term Key value pair
     * @return \Elastica\Filter\Term Filter object
     */
    public function setRawTerm(array $term)
    {
        return $this->setParams($term);
    }

    /**
     * Adds a term to the term query
     *
     * @param  string                     $key   Key to query
     * @param  string|array               $value Values(s) for the query. Boost can be set with array
     * @return \Elastica\Filter\Term Filter object
     */
    public function setTerm($key, $value)
    {
        return $this->setRawTerm(array($key => $value));
    }
}
