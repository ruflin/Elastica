<?php

namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Bool Filter.
 *
 * This class is for backward compatibility reason for all php < 7 versions. For PHP 7 and above use BoolFilter as Bool is reserved.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @deprecated Use BoolFilter instead. From PHP7 bool is reserved word and this class will be removed in further Elastica releases
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-filter.html
 */
class Bool extends BoolFilter
{
}
