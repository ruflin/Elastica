<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * Class Min.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-min-aggregation.html
 */
class Min extends AbstractSimpleAggregation
{
    use Traits\MissingTrait;
}
