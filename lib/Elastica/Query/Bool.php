<?php
namespace Elastica\Query;

/**
 * Bool query.
 *
 * This class is for backward compatibility reason for all php < 7 versions. For PHP 7 and above use BoolFilter as Bool is reserved.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 */
class Bool extends BoolQuery
{
}
