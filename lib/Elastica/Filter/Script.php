<?php

/**
 * Script filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/script-filter.html
 */
class Elastica_Filter_Script extends Elastica_Filter_Abstract
{
    /**
     * Construct script filter
     *
     * @param array|string|Elastica_Script $script OPTIONAL Script
     */
    public function __construct($script = null)
    {
        if ($script) {
            $this->setScript($script);
        }
    }

    /**
     * Sets query object
     *
     * @deprecated
     * @param  string|array|Elastica_Query_Abstract $query
     * @return Elastica_Filter_Script
     */
    public function setQuery($query)
    {
        if ($query instanceof Elastica_Query_Abstract) {
            $query = $query->toArray();
        }
        return $this->setScript($query);
    }

    /**
     * Sets script object
     *
     * @param Elastica_Script|string|array $script
     * @return Elastica_Filter_Script
     */
    public function setScript($script)
    {
        $script = Elastica_Script::create($script);
        return $this->setParams($script->toArray());
    }
}
