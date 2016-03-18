<?php

namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Type Filter.
 *
 * @author James Wilson <jwilson556@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-type-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
class Type extends AbstractFilter
{
    /**
     * Type name.
     *
     * @var string
     */
    protected $_type = null;

    /**
     * Construct Type Filter.
     *
     * @param string $type Type name
     */
    public function __construct($type = null)
    {
        if ($type) {
            $this->setType($type);
        }
    }

    /**
     * Ads a field with arguments to the range query.
     *
     * @param string $typeName Type name
     *
     * @return $this
     */
    public function setType($typeName)
    {
        $this->_type = $typeName;

        return $this;
    }

    /**
     * Convert object to array.
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     *
     * @return array Filter array
     */
    public function toArray()
    {
        return array(
            'type' => array('value' => $this->_type),
        );
    }
}
