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
     * returns true if $name is supported, false otherwise.
     *
     * @param string $name
     * @param string $type
     *
     * @return bool
     */
    public function supports(string $name, string $type): bool
    {
        switch ($type) {
            case DSL::TYPE_QUERY:
                return \in_array($name, $this->queries, true);
            case DSL::TYPE_AGGREGATION:
                return \in_array($name, $this->aggregations, true);
            case DSL::TYPE_SUGGEST:
                return \in_array($name, $this->suggesters, true);
        }

        // disables version check in Facade for custom DSL objects
        return true;
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
}
