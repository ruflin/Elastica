<?php

namespace Elastica\Aggregation;

/**
 * Class Terms
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-terms-aggregation.html
 */
class Terms extends AbstractSimpleAggregation
{
    /**
     * Set the bucket sort order
     * @param  string $order     "_count", "_term", or the name of a sub-aggregation or sub-aggregation response field
     * @param  string $direction "asc" or "desc"
     * @return Terms
     */
    public function setOrder($order, $direction)
    {
        return $this->setParam("order", array($order => $direction));
    }

    /**
     * Set the minimum number of documents in which a term must appear in order to be returned in a bucket
     * @param  int   $count
     * @return Terms
     */
    public function setMinimumDocumentCount($count)
    {
        return $this->setParam("min_doc_count", $count);
    }

    /**
     * Filter documents to include based on a regular expression
     * @param  string $pattern a regular expression
     * @param  string $flags   Java Pattern flags
     * @return Terms
     */
    public function setInclude($pattern, $flags = null)
    {
        if (is_null($flags)) {
            return $this->setParam("include", $pattern);
        }

        return $this->setParam("include", array(
            "pattern" => $pattern,
            "flags" => $flags,
        ));
    }

    /**
     * Filter documents to exclude based on a regular expression
     * @param  string $pattern a regular expression
     * @param  string $flags   Java Pattern flags
     * @return Terms
     */
    public function setExclude($pattern, $flags = null)
    {
        if (is_null($flags)) {
            return $this->setParam("exclude", $pattern);
        }

        return $this->setParam("exclude", array(
            "pattern" => $pattern,
            "flags" => $flags,
        ));
    }

    /**
     * Sets the amount of terms to be returned.
     * @param  int                         $size The amount of terms to be returned.
     * @return \Elastica\Aggregation\Terms
     */
    public function setSize($size)
    {
        return $this->setParam('size', $size);
    }

    /**
     * Sets how many terms the coordinating node will request from each shard.
     * @param  int                         $shard_size The amount of terms to be returned.
     * @return \Elastica\Aggregation\Terms
     */
    public function setShardSize($shard_size)
    {
        return $this->setParam('shard_size', $shard_size);
    }

    /**
     * Instruct Elasticsearch to use direct field data or ordinals of the field values to execute this aggregation.
     * The execution hint will be ignored if it is not applicable.
     * @param  string $hint map or ordinals
     * @return Terms
     */
    public function setExecutionHint($hint)
    {
        return $this->setParam("execution_hint", $hint);
    }
}
