<?php
/**
 * Fuzzy query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/fuzzy-query.html
 */
class Elastica_Query_Fuzzy extends Elastica_Query_Abstract
{
    /**
     * Adds field to fuzzy query
     *
     * @param  string               $fieldName Field name
     * @param  array                $args      Data array
     * @return Elastica_Query_Fuzzy Current object
     */
    public function addField($fieldName, array $args)
    {
        return $this->setParam($fieldName, $args);
    }
}
