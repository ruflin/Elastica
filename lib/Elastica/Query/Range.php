<?php
/**
 * Range query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/range-query.html
 */
class Elastica_Query_Range extends Elastica_Query_Abstract
{
    /**
     * Constructor
     *
     * @param string    $fieldName  Field name
     * @param array     $args       Field arguments
     */
    public function __construct($fieldName = null, array $args = array())
    {
        if ($fieldName) {
            $this->addField($fieldName, $args);
        }
    }

    /**
     * Adds a range field to the query
     *
     * @param  string               $fieldName Field name
     * @param  array                $args      Field arguments
     * @return Elastica_Query_Range Current object
     */
    public function addField($fieldName, array $args)
    {
        return $this->setParam($fieldName, $args);
    }
}
