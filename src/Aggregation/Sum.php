<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * Class Sum.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-sum-aggregation.html
 */
class Sum extends AbstractSimpleAggregation
{
    use Traits\MissingTrait;
}
