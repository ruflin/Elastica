<?php

namespace Elastica\Aggregation;

/**
 * Class ScriptedMetric.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-scripted-metric-aggregation.html
 */
class ScriptedMetric extends AbstractAggregation
{
    /**
     * @param string      $name          the name if this aggregation
     * @param string|null $initScript    Executed prior to any collection of documents
     * @param string|null $mapScript     Executed once per document collected
     * @param string|null $combineScript Executed once on each shard after document collection is complete
     * @param string|null $reduceScript  Executed once on the coordinating node after all shards have returned their results
     */
    public function __construct(
        string $name,
        ?string $initScript = null,
        ?string $mapScript = null,
        ?string $combineScript = null,
        ?string $reduceScript = null
    ) {
        parent::__construct($name);
        if ($initScript) {
            $this->setInitScript($initScript);
        }
        if ($mapScript) {
            $this->setMapScript($mapScript);
        }
        if ($combineScript) {
            $this->setCombineScript($combineScript);
        }
        if ($reduceScript) {
            $this->setReduceScript($reduceScript);
        }
    }

    /**
     * Executed once on each shard after document collection is complete.
     *
     * Allows the aggregation to consolidate the state returned from each shard.
     * If a combine_script is not provided the combine phase will return the aggregation variable.
     *
     * @return $this
     */
    public function setCombineScript(string $script): self
    {
        return $this->setParam('combine_script', $script);
    }

    /**
     * Executed prior to any collection of documents.
     *
     * Allows the aggregation to set up any initial state.
     *
     * @return $this
     */
    public function setInitScript(string $script): self
    {
        return $this->setParam('init_script', $script);
    }

    /**
     * Executed once per document collected.
     *
     * This is the only required script. If no combine_script is specified, the resulting state needs to be stored in
     * an object named _agg.
     *
     * @return $this
     */
    public function setMapScript(string $script): self
    {
        return $this->setParam('map_script', $script);
    }

    /**
     * Executed once on the coordinating node after all shards have returned their results.
     *
     * The script is provided with access to a variable _aggs which is an array of the result of the combine_script on
     * each shard. If a reduce_script is not provided the reduce phase will return the _aggs variable.
     *
     * @return $this
     */
    public function setReduceScript(string $script): self
    {
        return $this->setParam('reduce_script', $script);
    }
}
