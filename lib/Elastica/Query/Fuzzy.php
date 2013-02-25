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
        $numericKeys = array_filter(array_keys($args), function ($key)
        {
            return is_numeric($key);
        });
        if (!empty($numericKeys))
        {
            throw new \InvalidArgumentException('Fuzzy does not accept two dimensional arrays.');
        }
        return $this->setParam($fieldName, $args);
    }
}
