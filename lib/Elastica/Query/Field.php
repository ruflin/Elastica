<?php
/**
 * Field query
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/field_query/
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Field extends Elastica_Query_Abstract
{
    public function toArray() {      
        return array('field' => $args);
    }
}
