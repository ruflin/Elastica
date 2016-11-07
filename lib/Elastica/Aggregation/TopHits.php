<?php
namespace Elastica\Aggregation;

use Elastica\Script\AbstractScript;
use Elastica\Script\ScriptFields;

/**
 * Class TopHits.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html
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
     * @param int $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        return $this->setParam('size', (int) $size);
    }

    /**
     * The offset from the first result you want to fetch.
     *
     * @param int $from
     *
     * @return $this
     */
    public function setFrom($from)
    {
        return $this->setParam('from', (int) $from);
    }

    /**
     * How the top matching hits should be sorted. By default the hits are sorted by the score of the main query.
     *
     * @param array $sortArgs
     *
     * @return $this
     */
    public function setSort(array $sortArgs)
    {
        return $this->setParam('sort', $sortArgs);
    }

    /**
     * Allows to control how the _source field is returned with every hit.
     *
     * @param array|bool $params Fields to be returned or false to disable source
     *
     * @return $this
     */
    public function setSource($params)
    {
        return $this->setParam('_source', $params);
    }

    /**
     * Returns a version for each search hit.
     *
     * @param bool $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        return $this->setParam('version', (bool) $version);
    }

    /**
     * Enables explanation for each hit on how its score was computed.
     *
     * @param bool $explain
     *
     * @return $this
     */
    public function setExplain($explain)
    {
        return $this->setParam('explain', (bool) $explain);
    }

    /**
     * Set script fields.
     *
     * @param array|\Elastica\Script\ScriptFields $scriptFields
     *
     * @return $this
     */
    public function setScriptFields($scriptFields)
    {
        if (is_array($scriptFields)) {
            $scriptFields = new ScriptFields($scriptFields);
        }

        return $this->setParam('script_fields', $scriptFields);
    }

    /**
     * Adds a Script to the aggregation.
     *
     * @param string                          $name
     * @param \Elastica\Script\AbstractScript $script
     *
     * @return $this
     */
    public function addScriptField($name, AbstractScript $script)
    {
        if (!isset($this->_params['script_fields'])) {
            $this->_params['script_fields'] = new ScriptFields();
        }

        $this->_params['script_fields']->addScript($name, $script);

        return $this;
    }

    /**
     * Sets highlight arguments for the results.
     *
     * @param array $highlightArgs
     *
     * @return $this
     */
    public function setHighlight(array $highlightArgs)
    {
        return $this->setParam('highlight', $highlightArgs);
    }

    /**
     * Allows to return the field data representation of a field for each hit.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setFieldDataFields(array $fields)
    {
        return $this->setParam('docvalue_fields', $fields);
    }
}
