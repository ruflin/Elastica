<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Param;

abstract class AbstractAggregation extends Param
{
    /**
     * @var string The name of this aggregation
     */
    protected $_name;

    /**
     * @var array Subaggregations belonging to this aggregation
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
     * Set the name of this aggregation.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * Retrieve the name of this aggregation.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Retrieve all subaggregations belonging to this aggregation.
     *
     * @return array
     */
    public function getAggs()
    {
        return $this->_aggs;
    }

    /**
     * Add a sub-aggregation.
     *
     * @param AbstractAggregation $aggregation
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addAggregation(AbstractAggregation $aggregation)
    {
        if ($aggregation instanceof GlobalAggregation) {
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
            $array = array('global' => new \stdClass());
        }
        if (sizeof($this->_aggs)) {
            $array['aggs'] = $this->_aggs;
        }

        return $array;
    }
}
