<?php

namespace Elastica\Filter;

/**
 * Exists query
 *
 * @category Xodoa
 * @package Elastica
 * @author Oleg Cherniy <oleg.cherniy@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/exists-filter.html
 */
class Exists extends AbstractFilter
{
    /**
     * Construct exists filter
     *
     * @param string $field
     */
    public function __construct($field)
    {
        $this->setField($field);
    }

    /**
     * Set field
     *
     * @param  string                  $field
     * @return \Elastica\Filter\Exists
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }
}
