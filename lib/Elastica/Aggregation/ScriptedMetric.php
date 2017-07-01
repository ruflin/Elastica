<?php
namespace Elastica\Aggregation;

/**
 * Class ScriptedMetric.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-scripted-metric-aggregation.html
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
    public function __construct($name, $initScript = null, $mapScript = null, $combineScript = null, $reduceScript = null)
    {
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
     * @param string $script
     *
     * @return $this
     */
    public function setCombineScript($script)
    {
        return $this->setParam('combine_script', $script);
    }

    /**
     * Executed prior to any collection of documents.
     *
     * Allows the aggregation to set up any initial state.
     *
     * @param string $script
     *
     * @return $this
     */
    public function setInitScript($script)
    {
        return $this->setParam('init_script', $script);
    }

    /**
     * Executed once per document collected.
     *
     * This is the only required script. If no combine_script is specified, the resulting state needs to be stored in
     * an object named _agg.
     *
     * @param string $script
     *
     * @return $this
     */
    public function setMapScript($script)
    {
        return $this->setParam('map_script', $script);
    }

    /**
     * Executed once on the coordinating node after all shards have returned their results.
     *
     * The script is provided with access to a variable _aggs which is an array of the result of the combine_script on
     * each shard. If a reduce_script is not provided the reduce phase will return the _aggs variable.
     *
     * @param string $script
     *
     * @return $this
     */
    public function setReduceScript($script)
    {
        return $this->setParam('reduce_script', $script);
    }
}
