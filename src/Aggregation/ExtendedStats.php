<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * Class ExtendedStats.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-extendedstats-aggregation.html
 */
class ExtendedStats extends AbstractSimpleAggregation
{
    use Traits\MissingTrait;
}
