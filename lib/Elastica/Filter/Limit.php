<?php
/**
 * Limit Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/limit-filter.html
 */
class Elastica_Filter_Limit extends Elastica_Filter_Abstract
{
    /**
     * Construct limit filter
     *
     * @param  int                   $limit Limit
     * @return Elastica_Filter_Limit
     */
    public function __construct($limit)
    {
        $this->setLimit($limit);
    }

    /**
     * Set the limit
     *
     * @param  int                   $limit Limit
     * @return Elastica_Filter_Limit
     */
    public function setLimit($limit)
    {
        return $this->setParam('value', (int) $limit);
    }
}
