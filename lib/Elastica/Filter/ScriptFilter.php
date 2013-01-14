<?php

namespace Elastica\Filter;

use Elastica\Query\AbstractQuery;
use Elastica\Script;

/**
 * Script filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/script-filter.html
 */
class ScriptFilter extends AbstractFilter
{
    /**
     * Query object
     *
     * @var array|Elastica\Query\AbstractQuery
     */
    protected $_query = null;

    /**
     * Construct script filter
     *
     * @param array|string|Elastica\Script $script OPTIONAL Script
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
     * @param  string|array|Elastica\Query\AbstractQuery $query
     * @return Elastica\Filter\ScriptFilter
     */
    public function setQuery($query)
    {
        if ($query instanceof AbstractQuery) {
            $query = $query->toArray();
        }

        return $this->setScript($query);
    }

    /**
     * Sets script object
     *
     * @param  Elastica\Script|string|array $script
     * @return Elastica\Filter\ScriptFilter
     */
    public function setScript($script)
    {
        $script = Script::create($script);

        return $this->setParams($script->toArray());
    }
}
