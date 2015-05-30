<?php
namespace Elastica\Filter;

/**
 * Not Filter.
 *
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-not-filter.html
 */
class BoolNot extends AbstractFilter
{
    /**
     * Creates Not filter query.
     *
     * @param \Elastica\Filter\AbstractFilter $filter Filter object
     */
    public function __construct(AbstractFilter $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * Set filter.
     *
     * @param \Elastica\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'not';
    }
}
