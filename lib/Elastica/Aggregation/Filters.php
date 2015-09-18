<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;

/**
 * Class Filters.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
 */
class Filters extends AbstractAggregation
{
    const NAMED_TYPE = 1;
    const ANONYMOUS_TYPE = 2;

    /**
     * @var int Type of bucket keys - named, or anonymous
     */
    private $_type = null;

    /**
     * Add a filter.
     *
     * If a name is given, it will be added as a key, otherwise considered as an anonymous filter
     *
     * @param AbstractFilter $filter
     * @param string         $name
     *
     * @return $this
     */
    public function addFilter(AbstractFilter $filter, $name = null)
    {
        if (null !== $name && !is_string($name)) {
            throw new InvalidException('Name must be a string');
        }

        $filterArray = array();

        $type = self::NAMED_TYPE;

        if (null === $name) {
            $filterArray[] = $filter;
            $type = self::ANONYMOUS_TYPE;
        } else {
            $filterArray[$name] = $filter;
        }

        if ($this->hasParam('filters')
            && count($this->getParam('filters'))
            && $this->_type !== $type
        ) {
            throw new InvalidException('Mix named and anonymous keys are not allowed');
        }

        $this->_type = $type;

        return $this->addParam('filters', $filterArray);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $filters = $this->getParam('filters');

        foreach ($filters as $filter) {
            if (self::NAMED_TYPE === $this->_type) {
                $key = key($filter);
                $array['filters']['filters'][$key] = current($filter)->toArray();
            } else {
                $array['filters']['filters'][] = current($filter)->toArray();
            }
        }

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
