<?php

namespace Elastica\Filter;

use Elastica\Exception\InvalidException;
use Elastica\Param;

/**
 * Abstract filter object. Should be extended by all filter types
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/
 */
abstract class AbstractFilter extends Param
{
    /**
     * Sets the filter cache
     *
     * @param  boolean                        $cached Cached
     * @return \Elastica\Filter\AbstractFilter
     */
    public function setCached($cached = true)
    {
        return $this->setParam('_cache', (bool) $cached);
    }

    /**
     * Sets the filter cache key
     *
     * @param  string                              $cacheKey Cache key
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Filter\AbstractFilter
     */
    public function setCacheKey($cacheKey)
    {
        $cacheKey = (string) $cacheKey;

        if (empty($cacheKey)) {
            throw new InvalidException('Invalid parameter. Has to be a non empty string');
        }

        return $this->setParam('_cache_key', (string) $cacheKey);
    }

    /**
     * Sets the filter name
     *
     * @param  string                         $name Name
     * @return \Elastica\Filter\AbstractFilter
     */
    public function setName($name)
    {
        return $this->setParam('_name', $name);
    }
}
