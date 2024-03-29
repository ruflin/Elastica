<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * Class Stats.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-stats-aggregation.html
 */
class Stats extends AbstractSimpleAggregation
{
    use Traits\MissingTrait;
}
