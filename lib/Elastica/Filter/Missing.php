<?php

namespace Elastica\Filter;

/**
 * Missing Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Maciej Wiercinski <maciej@wiercinski.net>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/missing-filter.html
 */
class Missing extends AbstractFilter
{
    /**
     * Construct missing filter
     *
     * @param string $field OPTIONAL
     */
    public function __construct($field = '')
    {
        if (strlen($field)) {
            $this->setField($field);
        }
    }

    /**
     * Set field
     *
     * @param  string                        $field
     * @return \Elastica\Filter\Missing
     */
    public function setField($field)
    {
        return $this->setParam('field', (string) $field);
    }
}
