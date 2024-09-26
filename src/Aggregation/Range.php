<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * Class Range.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-range-aggregation.html
 */
class Range extends AbstractSimpleAggregation
{
    use Traits\KeyedTrait;
    use Traits\RangeTrait;
}
