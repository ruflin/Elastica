<?php

namespace Elastica\Filter;

use Elastica\Exception\InvalidException;

/**
 * Terms filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/terms-filter.html
 */
class Terms extends AbstractFilter
{
    /**
     * Terms
     *
     * @var array Terms
     */
    protected $_terms = array();

    /**
     * Params
     *
     * @var array Params
     */
    protected $_params = array();

    /**
     * Terms key
     *
     * @var string Terms key
     */
    protected $_key = '';

    /**
     * Creates terms filter
     *
     * @param string $key   Terms key
     * @param array  $terms Terms values
     */
    public function __construct($key = '', array $terms = array())
    {
        $this->setTerms($key, $terms);
    }

    /**
     * Sets key and terms for the filter
     *
     * @param  string                      $key   Terms key
     * @param  array                       $terms Terms for the query.
     * @return \Elastica\Filter\Terms
     */
    public function setTerms($key, array $terms)
    {
        $this->_key = $key;
        $this->_terms = array_values($terms);

        return $this;
    }

    /**
     * Adds an additional term to the query
     *
     * @param  string                      $term Filter term
     * @return \Elastica\Filter\Terms Filter object
     */
    public function addTerm($term)
    {
        $this->_terms[] = $term;

        return $this;
    }

    /**
     * Converts object to an array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     * @throws \Elastica\Exception\InvalidException
     * @return array                               data array
     */
    public function toArray()
    {
        if (empty($this->_key)) {
            throw new InvalidException('Terms key has to be set');
        }
        $this->_params[$this->_key] = $this->_terms;

        return array('terms' => $this->_params);
    }
}
