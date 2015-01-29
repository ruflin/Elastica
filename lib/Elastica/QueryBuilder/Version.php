<?php

namespace Elastica\QueryBuilder;

/**
 * Abstract Version class
 *
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
abstract class Version
{
    /**
     * supported query methods
     *
     * @var string[]
     */
    protected $queries = array();

    /**
     * supported filter methods
     *
     * @var string[]
     */
    protected $filters = array();

    /**
     * supported aggregation methods
     *
     * @var string[]
     */
    protected $aggregations = array();

    /**
     * supported $suggester methods
     *
     * @var string[]
     */
    protected $suggesters = array();

    /**
     * returns true if $name is supported, false otherwise
     *
     * @param  string $name
     * @param $type
     * @return bool
     */
    public function supports($name, $type)
    {
        switch ($type) {
            case DSL::TYPE_QUERY:
                $supports = in_array($name, $this->queries);
                break;
            case DSL::TYPE_FILTER:
                $supports = in_array($name, $this->filters);
                break;
            case DSL::TYPE_AGGREGATION:
                $supports = in_array($name, $this->aggregations);
                break;
            case DSL::TYPE_SUGGEST:
                $supports = in_array($name, $this->suggesters);
                break;
            default:
                // disables version check in Facade for custom DSL objects
                $supports = true;
        }

        return $supports;
    }

    /**
     * @return string[]
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @return string[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return string[]
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @return string[]
     */
    public function getSuggesters()
    {
        return $this->suggesters;
    }
}
