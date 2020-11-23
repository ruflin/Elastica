<?php

namespace Elastica\Aggregation;

/**
 * Class Avg.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-avg-aggregation.html
 */
class Avg extends AbstractSimpleAggregation
{
    use Traits\MissingTrait;
}
