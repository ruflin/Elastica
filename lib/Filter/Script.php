<?php

namespace Elastica\Filter;

use Elastica;
use Elastica\Query\AbstractQuery;

/**
 * Script filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/script-filter.html
 */
class Script extends AbstractFilter
{
    /**
     * Query object
     *
     * @var array|\Elastica\Query\AbstractQuery
     */
    protected $_query = null;

    /**
     * Construct script filter
     *
     * @param array|string|\Elastica\Script $script OPTIONAL Script
     */
    public function __construct($script = null)
    {
        if ($script) {
            $this->setScript($script);
        }
    }

    /**
     * Sets script object
     *
     * @param  \Elastica\Script|string|array $script
     * @return \Elastica\Filter\Script
     */
    public function setScript($script)
    {
        $script = Elastica\Script::create($script);

        return $this->setParams($script->toArray());
    }
}
