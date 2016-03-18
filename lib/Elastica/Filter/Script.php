<?php

namespace Elastica\Filter;

use Elastica;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Script filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-script-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
class Script extends AbstractFilter
{
    /**
     * Query object.
     *
     * @var array|\Elastica\Query\AbstractQuery
     */
    protected $_query = null;

    /**
     * Construct script filter.
     *
     * @param array|string|\Elastica\Script\AbstractScript $script OPTIONAL Script
     */
    public function __construct($script = null)
    {
        if ($script) {
            $this->setScript($script);
        }
    }

    /**
     * Sets script object.
     *
     * @param \Elastica\Script\Script|string|array $script
     *
     * @return $this
     */
    public function setScript($script)
    {
        return $this->setParam('script', Elastica\Script\Script::create($script));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (isset($array['script'])) {
            $array['script'] = $array['script']['script'];
        }

        return $array;
    }
}
