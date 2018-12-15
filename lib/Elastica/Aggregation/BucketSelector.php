<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class BucketSelector.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-selector-aggregation.html
 */
class BucketSelector extends AbstractSimpleAggregation
{
    /**
     * @param string      $name
     * @param array|null  $bucketsPath
     * @param string|null $script
     */
    public function __construct(string $name, array $bucketsPath = null, string $script = null)
    {
        parent::__construct($name);

        if ($bucketsPath !== null) {
            $this->setBucketsPath($bucketsPath);
        }

        if ($script !== null) {
            $this->setScript($script);
        }
    }

    /**
     * Set the buckets_path for this aggregation.
     *
     * @param array $bucketsPath
     *
     * @return $this
     */
    public function setBucketsPath($bucketsPath)
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    /**
     * Set the gap policy for this aggregation.
     *
     * @param string $gapPolicy
     *
     * @return $this
     */
    public function setGapPolicy(string $gapPolicy = 'skip')
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }
}

