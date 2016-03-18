<?php

namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use BoolQuery::addShould. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Or Filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-or-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
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
