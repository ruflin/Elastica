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
     * Set the field for this aggregation.
     *
     * @param string $script the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setCombineScript($script)
    {
        return $this->setParam('combine_script', $script);
    }

    /**
     * Set the field for this aggregation.
     *
     * @param string $script the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setInitScript($script)
    {
        return $this->setParam('init_script', $script);
    }

    /**
     * Set the field for this aggregation.
     *
     * @param string $script the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setMapScript($script)
    {
        return $this->setParam('map_script', $script);
    }

    /**
     * Set the field for this aggregation.
     *
     * @param string $script the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setReduceScript($script)
    {
        return $this->setParam('reduce_script', $script);
    }
}
