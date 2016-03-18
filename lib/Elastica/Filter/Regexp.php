<?php

namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Regexp filter.
 *
 * @author Timothy Lamb <trash80@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
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
     * Holds the regexp options.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Create Regexp object.
     *
     * @param string $field   Field name
     * @param string $regexp  Regular expression
     * @param array  $options Regular expression options
     *
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct($field = '', $regexp = '', $options = array())
    {
        $this->setField($field);
        $this->setRegexp($regexp);
        $this->setOptions($options);
    }

    /**
     * Sets the name of the regexp field.
     *
     * @param string $field Field name
     *
     * @return $this
     */
    public function setField($field)
    {
        $this->_field = $field;

        return $this;
    }

    /**
     * Sets the regular expression query string.
     *
     * @param string $regexp Regular expression
     *
     * @return $this
     */
    public function setRegexp($regexp)
    {
        $this->_regexp = $regexp;

        return $this;
    }

    /**
     * Sets the regular expression query options.
     *
     * @param array $options Regular expression options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * Converts object to an array.
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     *
     * @return array data array
     */
    public function toArray()
    {
        if (count($this->_options) > 0) {
            $options = array('value' => $this->_regexp);
            $options = array_merge($options, $this->_options);

            $this->setParam($this->_field, $options);
        } else {
            $this->setParam($this->_field, $this->_regexp);
        }

        return parent::toArray();
    }
}
