<?php

namespace Elastica\Aggregation;


use Elastica\Param;

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
     * @return AbstractAggregation
     */
    public function addAggregation(AbstractAggregation $aggregation)
    {
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
            if(empty($array['global_aggregation']))
                $array = array('global' => new \stdClass());
            else
                $array = array('global' => $array['global_aggregation']);
        }
        if (sizeof($this->_aggs)) {
            $array['aggs'] = $this->_aggs;
        }
        return $array;
    }
}