<?php

namespace Elastica\Aggregation;

use Elastica\Param;
use Elastica\Exception\InvalidException;

abstract class AbstractAggregation extends Param
{
    /**
     * The name of this aggregation
     * @var string
     */
    protected $_name;

    /**
     * Subaggregations belonging to this aggregation
     * @var array
     */
    protected $_aggs = array();

    /**
     * @param string $name the name of this aggregation
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Set the name of this aggregation
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Retrieve the name of this aggregation
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Retrieve all subaggregations belonging to this aggregation
     * @return array
     */
    public function getAggs()
    {
        return $this->_aggs;
    }

    /**
     * Add a sub-aggregation
     * @param AbstractAggregation $aggregation
     * @throws \Elastica\Exception\InvalidException
     * @return AbstractAggregation
     */
    public function addAggregation(AbstractAggregation $aggregation)
    {
        if(is_a($aggregation, 'Elastica\Aggregation\GlobalAggregation')) {
            throw new InvalidException('Global aggregators can only be placed as top level aggregators');
        }

        $this->_aggs[$aggregation->getName()] = $aggregation->toArray();
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        if (array_key_exists('global_aggregation', $array)) {
            // compensate for class name GlobalAggregation
            $array = array('global' => new \stdClass);
        }
        if (sizeof($this->_aggs)) {
            $array['aggs'] = $this->_aggs;
        }
        return $array;
    }
}