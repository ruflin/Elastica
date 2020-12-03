<?php

namespace Elastica\Aggregation;

/**
 * Class Sampler.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-sampler-aggregation.html
 */
class Sampler extends AbstractAggregation
{
    use Traits\ShardSizeTrait;
}
