<?php

namespace Elastica\Filter;

use Elastica\Exception\InvalidException;
use Elastica\Param;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Abstract filter object. Should be extended by all filter types.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filters.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
abstract class AbstractFilter extends Param
{
    /**
     * Sets the filter cache.
     *
     * @param bool $cached Cached
     *
     * @return $this
     */
    public function setCached($cached = true)
    {
        return $this->setParam('_cache', (bool) $cached);
    }

    /**
     * Sets the filter cache key.
     *
     * @param string $cacheKey Cache key
     *
     * @throws \Elastica\Exception\InvalidException If given key is empty
     *
     * @return $this
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
     * Sets the filter name.
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function setName($name)
    {
        return $this->setParam('_name', $name);
    }
}
