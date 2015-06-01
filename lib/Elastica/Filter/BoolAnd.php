<?php
namespace Elastica\Filter;

/**
 * And Filter.
 *
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-and-filter.html
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
