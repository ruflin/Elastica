<?php

namespace Elastica\Aggregation;

/**
 * Class Max.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-max-aggregation.html
 */
class Max extends AbstractSimpleAggregation
{
    use Traits\MissingTrait;
}
