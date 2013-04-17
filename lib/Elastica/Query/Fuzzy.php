<?php

namespace Elastica\Query;

/**
 * Fuzzy query
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/fuzzy-query.html
 */
class Fuzzy extends AbstractQuery
{
    /**
     * Adds field to fuzzy query
     *
     * @param  string                    $fieldName Field name
     * @param  array                     $args      Data array
     * @return \Elastica\Query\Fuzzy Current object
     */
    public function addField($fieldName, array $args)
    {
        return $this->setParam($fieldName, $args);
    }
}
