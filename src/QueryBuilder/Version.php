<?php

namespace Elastica\QueryBuilder;

/**
 * Abstract Version class.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
abstract class Version
{
    /**
     * supported query methods.
     *
     * @var string[]
     */
    protected $queries = [];

    /**
     * supported filter methods.
     *
     * @var string[]
     */
    protected $filters = [];

    /**
     * supported aggregation methods.
     *
     * @var string[]
     */
    protected $aggregations = [];

    /**
     * supported $suggester methods.
     *
     * @var string[]
     */
    protected $suggesters = [];

    /**
     * supported methods for field collapsing.
     *
     * @var array
     */
    protected $collapsers = [];

    /**
     * returns true if $name is supported, false otherwise.
     */
    public function supports(string $name, string $type): bool
    {
        return match ($type) {
            DSL::TYPE_QUERY => \in_array($name, $this->queries, true),
            DSL::TYPE_AGGREGATION => \in_array($name, $this->aggregations, true),
            DSL::TYPE_SUGGEST => \in_array($name, $this->suggesters, true),
            DSL::TYPE_COLLAPSE => \in_array($name, $this->collapsers, true),
            default => true,
        };
    }

    /**
     * @return string[]
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @return string[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return string[]
     */
    public function getSuggesters(): array
    {
        return $this->suggesters;
    }

    /**
     * @return string[]
     */
    public function getCollapsers(): array
    {
        return $this->collapsers;
    }
}
