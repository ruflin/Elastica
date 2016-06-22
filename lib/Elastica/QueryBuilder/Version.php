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
    protected $queries = array();

    /**
     * supported filter methods.
     *
     * @var string[]
     */
    protected $filters = array();

    /**
     * supported aggregation methods.
     *
     * @var string[]
     */
    protected $aggregations = array();

    /**
     * supported $suggester methods.
     *
     * @var string[]
     */
    protected $suggesters = array();

    /**
     * returns true if $name is supported, false otherwise.
     *
     * @param string $name
     * @param $type
     *
     * @return bool
     */
    public function supports($name, $type)
    {
        switch ($type) {
            case DSL::TYPE_QUERY:
                return in_array($name, $this->queries);
            case DSL::TYPE_FILTER:
                return in_array($name, $this->filters);
            case DSL::TYPE_AGGREGATION:
                return in_array($name, $this->aggregations);
            case DSL::TYPE_SUGGEST:
                return in_array($name, $this->suggesters);
        }

        // disables version check in Facade for custom DSL objects
        return true;
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
