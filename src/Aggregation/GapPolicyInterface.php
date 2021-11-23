<?php

namespace Elastica\Aggregation;

use Elastica\Aggregation\Traits\GapPolicyTrait;

/**
 * Dealing with gaps in the data.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline.html#gap-policy
 * @see GapPolicyTrait
 */
interface GapPolicyInterface
{
    /**
     * Treats missing data as if the bucket does not exist. It will skip the bucket and continue calculating using the next available value.
     */
    public const SKIP = 'skip';

    /**
     * Will replace missing values with a zero (0) and pipeline aggregation computation will proceed as normal.
     */
    public const INSERT_ZEROS = 'insert_zeros';

    /**
     * Is similar to skip, except if the metric provides a non-null, non-NaN value this value is used, otherwise the empty bucket is skipped.
     */
    public const KEEP_VALUES = 'keep_values';

    /**
     * Set the gap policy for this aggregation.
     *
     * @return $this
     */
    public function setGapPolicy(string $gapPolicy);
}
