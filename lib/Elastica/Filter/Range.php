<?php
namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

use Elastica\Exception\InvalidException;

/**
 * Range Filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
class Range extends AbstractFilter
{
    /**
     * Fields.
     *
     * @var array Fields
     */
    protected $_fields = [];

    /**
     * Construct range filter.
     *
     * @param string $fieldName Field name
     * @param array  $args      Field arguments
     */
    public function __construct($fieldName = '', array $args = [])
    {
        if ($fieldName) {
            $this->addField($fieldName, $args);
        }
    }

    /**
     * Ads a field with arguments to the range query.
     *
     * @param string $fieldName Field name
     * @param array  $args      Field arguments
     *
     * @return $this
     */
    public function addField($fieldName, array $args)
    {
        $this->_fields[$fieldName] = $args;

        return $this;
    }

    /**
     * Set execution mode.
     *
     * @param string $execution Options: "index" or "fielddata"
     *
     * @throws \Elastica\Exception\InvalidException If you call this method.
     */
    public function setExecution($execution)
    {
        throw new InvalidException('The "execution" option for "range filter" was removed since Elasticsearch 2.0.');
    }

    /**
     * Converts object to array.
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     *
     * @return array Filter array
     */
    public function toArray()
    {
        $this->setParams(array_merge($this->getParams(), $this->_fields));

        return parent::toArray();
    }
}
