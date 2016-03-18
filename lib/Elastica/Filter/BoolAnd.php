<?php

namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use BoolQuery::addMust. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * And Filter.
 *
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-and-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
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
