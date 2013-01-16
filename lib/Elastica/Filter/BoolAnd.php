<?php

namespace Elastica\Filter;

/**
 * And Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/and-filter.html
 */
class BoolAnd extends AbstractMulti
{
    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'and';
    }
}
