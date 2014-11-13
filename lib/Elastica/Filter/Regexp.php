<?php

namespace Elastica\Filter;

/**
 * Regexp filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Timothy Lamb <trash80@gmail.com>
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-regexp-filter.html
 */
class Regexp extends AbstractFilter
{
    /**
     * Holds the name of the field for the regular expression.
     *
     * @var string
     */
    protected $_field = '';

    /**
     * Holds the regexp string.
     *
     * @var string
     */
    protected $_regexp = '';

    /**
     * Create Regexp object
     *
     * @param  string $field    Field name
     * @param  string $regexp   Regular expression
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct($field = '', $regexp = '')
    {
        $this->setField($field);
        $this->setRegexp($regexp);
    }

    /**
     * Sets the name of the regexp field.
     *
     * @param  string                       $field Field name
     * @return \Elastica\Filter\Regexp
     */
    public function setField($field)
    {
        $this->_field = $field;

        return $this;
    }

    /**
     * Sets the regular expression query string.
     *
     * @param  string                       $regexp Regular expression
     * @return \Elastica\Filter\Regexp
     */
    public function setRegexp($regexp)
    {
        $this->_regexp = $regexp;

        return $this;
    }

    /**
     * Converts object to an array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     * @return array data array
     */
    public function toArray()
    {
        $this->setParam($this->_field, $this->_regexp);

        return parent::toArray();
    }
}
