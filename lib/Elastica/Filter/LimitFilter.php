<?php

namespace Elastica\Filter;

/**
 * Limit Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/limit-filter.html
 */
class LimitFilter extends AbstractFilter
{
    /**
     * Construct limit filter
     *
     * @param  int                         $limit Limit
     * @return Elastica\Filter\LimitFilter
     */
    public function __construct($limit)
    {
        $this->setLimit($limit);
    }

    /**
     * Set the limit
     *
     * @param  int                         $limit Limit
     * @return Elastica\Filter\LimitFilter
     */
    public function setLimit($limit)
    {
        return $this->setParam('value', (int) $limit);
    }
}
