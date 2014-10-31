<?php

namespace Elastica\Filter;

/**
 * Or Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/or-filter.html
 */
class BoolOr extends AbstractMulti
{
    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'or';
    }
}
