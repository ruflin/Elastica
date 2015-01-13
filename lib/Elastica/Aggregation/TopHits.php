<?php
namespace Elastica\Aggregation;

use Elastica\Script;
use Elastica\ScriptFields;

/**
 * Class TopHits
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html
 */
class TopHits extends AbstractAggregation
{
    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        // if there are no params, it's ok, but ES will throw exception if json
        // will be like {"top_hits":[]} instead of {"top_hits":{}}
        if (empty($array['top_hits'])) {
            $array['top_hits'] = new \stdClass();
        }

        return $array;
    }

    /**
     * The maximum number of top matching hits to return per bucket. By default the top three matching hits are returned.
     *
     * @param  int  $size
     * @return self
     */
    public function setSize($size)
    {
        return $this->setParam('size', (int) $size);
    }

    /**
     * The offset from the first result you want to fetch.
     *
     * @param  int  $from
     * @return self
     */
    public function setFrom($from)
    {
        return $this->setParam('from', (int) $from);
    }

    /**
     * How the top matching hits should be sorted. By default the hits are sorted by the score of the main query.
     *
     * @param  array $sortArgs
     * @return self
     */
    public function setSort(array $sortArgs)
    {
        return $this->setParam('sort', $sortArgs);
    }

    /**
     * Allows to control how the _source field is returned with every hit.
     *
     * @param  array $fields
     * @return self
     */
    public function setSource(array $fields)
    {
        return $this->setParam('_source', $fields);
    }

    /**
     * Returns a version for each search hit.
     *
     * @param  bool $version
     * @return self
     */
    public function setVersion($version)
    {
        return $this->setParam('version', (bool) $version);
    }

    /**
     * Enables explanation for each hit on how its score was computed.
     *
     * @param  bool $explain
     * @return self
     */
    public function setExplain($explain)
    {
        return $this->setParam('explain', (bool) $explain);
    }

    /**
     * Set script fields
     *
     * @param  array|\Elastica\ScriptFields $scriptFields
     * @return self
     */
    public function setScriptFields($scriptFields)
    {
        if (is_array($scriptFields)) {
            $scriptFields = new ScriptFields($scriptFields);
        }

        return $this->setParam('script_fields', $scriptFields->toArray());
    }

    /**
     * Adds a Script to the aggregation
     *
     * @param  string           $name
     * @param  \Elastica\Script $script
     * @return self
     */
    public function addScriptField($name, Script $script)
    {
        $this->_params['script_fields'][$name] = $script->toArray();

        return $this;
    }

    /**
     * Sets highlight arguments for the results
     *
     * @param  array $highlightArgs
     * @return self
     */
    public function setHighlight(array $highlightArgs)
    {
        return $this->setParam('highlight', $highlightArgs);
    }

    /**
     * Allows to return the field data representation of a field for each hit
     *
     * @param  array $fields
     * @return self
     */
    public function setFieldDataFields(array $fields)
    {
        return $this->setParam('fielddata_fields', $fields);
    }
}
