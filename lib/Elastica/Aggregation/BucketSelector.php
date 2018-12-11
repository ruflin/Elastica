<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class BucketSelector.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-selector-aggregation.html
 */
class BucketSelector extends AbstractAggregation
{
    /**
     * @param string      $name
     * @param array|null  $bucketsPath
     * @param string|null $script
     */
    public function __construct($name, $bucketsPath = null, $script = null)
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
     * Set the script for this aggregation.
     *
     * @param string $script
     *
     * @return $this
     */
    public function setScript($script)
    {
        return $this->setParam('script', $script);
    }

    /**
     * Set the gap policy for this aggregation.
     *
     * @param string $gapPolicy
     *
     * @return $this
     */
    public function setGapPolicy($gapPolicy)
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }

    /**
     * @throws InvalidException If buckets path or script is not set
     *
     * @return array
     */
    public function toArray()
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        } elseif (!$this->hasParam('script')) {
            throw new InvalidException('Script parameter is required');
        }

        return parent::toArray();
    }
}

