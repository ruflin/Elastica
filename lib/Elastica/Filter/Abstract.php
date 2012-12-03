<?php
/**
 * Abstract filter object. Should be extended by all filter types
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/
 */
abstract class Elastica_Filter_Abstract extends Elastica_Param
{
    /**
     * Sets the filter cache
     *
     * @param  boolean                  $cached Cached
     * @return Elastica_Filter_Abstract
     */
    public function setCached($cached = true)
    {
        return $this->setParam('_cache', (bool) $cached);
    }

    /**
     * Sets the filter cache key
     *
     * @param  string                   $cacheKey Cache key
     * @throws Elastica_Exception_Invalid
     * @return Elastica_Filter_Abstract
     */
    public function setCacheKey($cacheKey)
    {
        $cacheKey = (string) $cacheKey;

        if (empty($cacheKey)) {
            throw new Elastica_Exception_Invalid('Invalid parameter. Has to be a non empty string');
        }

        return $this->setParam('_cache_key', (string) $cacheKey);
    }

    /**
     * Sets the filter name
     *
     * @param  string                   $name Name
     * @return Elastica_Filter_Abstract
     */
    public function setName($name)
    {
        return $this->setParam('_name', $name);
    }

    /**
     * Returns a filter made from this "and" argument
     *
     * @param  Elastica_Filter_Abstract $filter Filter to add
     * @return Elastica_Filter_Abstract
     */
    public function andFilter(Elastica_Filter_Abstract $filter)
    {
        if ($this instanceof Elastica_Filter_MatchAll) {
            return $filter;
        }

        if ($this instanceof Elastica_Filter_And) {
            return $this->addFilter($filter);
        }

        $result = new Elastica_Filter_And;

        return $result->addFilter($this)->addFilter($filter);
    }

    /**
     * Returns a filter made from this "or" argument
     *
     * @param  Elastica_Filter_Abstract $filter Filter to add
     * @return Elastica_Filter_Abstract
     */
    public function orFilter(Elastica_Filter_Abstract $filter)
    {
        if ($this instanceof Elastica_Filter_MatchAll) {
            return $filter;
        }

        if ($this instanceof Elastica_Filter_Or) {
            return $this->addFilter($filter);
        }

        $result = new Elastica_Filter_Or;

        return $result->addFilter($this)->addFilter($filter);
    }
}
