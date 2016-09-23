<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class SerialDiff.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-serialdiff-aggregation.html
 */
class SerialDiff extends AbstractAggregation
{
    /**
     * @param string      $name
     * @param string|null $bucketsPath
     */
    public function __construct($name, $bucketsPath = null)
    {
        parent::__construct($name);

        if ($bucketsPath !== null) {
            $this->setBucketsPath($bucketsPath);
        }
    }

    /**
     * Set the buckets_path for this aggregation.
     *
     * @param string $bucketsPath
     *
     * @return $this
     */
    public function setBucketsPath($bucketsPath)
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    /**
     * Set the lag for this aggregation.
     *
     * @param int $lag
     *
     * @return $this
     */
    public function setLag($lag)
    {
        return $this->setParam('lag', $lag);
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
     * Set the format for this aggregation.
     *
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        return $this->setParam('format', $format);
    }

    /**
     * @throws InvalidException If buckets path is not set
     *
     * @return array
     */
    public function toArray()
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        }

        return parent::toArray();
    }
}
