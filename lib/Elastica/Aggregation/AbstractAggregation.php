<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\NameableInterface;
use Elastica\Param;

abstract class AbstractAggregation extends Param implements NameableInterface
{
    /**
     * @var string The name of this aggregation
     */
    protected $_name;

    /**
     * @var array Aggregation metadata
     */
    protected $_metas = [];

    /**
     * @var array Subaggregations belonging to this aggregation
     */
    protected $_aggs = [];

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
     * Sets (overwrites) the value at the given key.
     *
     * @param string $key   Key to set
     * @param mixed  $value Key Value
     *
     * @return $this
     */
    public function setMeta($key, $value)
    {
        $this->_metas[$key] = $value;

        return $this;
    }

    /**
     * Sets (overwrites) all metas of this object.
     *
     * @param array $metas Meta list
     *
     * @return $this
     */
    public function setMetas(array $metas)
    {
        $this->_metas = $metas;

        return $this;
    }

    /**
     * Adds a meta to the list.
     *
     * This function can be used to add an array of metas
     *
     * @param string $key   Meta key
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    public function addMeta($key, $value)
    {
        $this->_metas[$key][] = $value;

        return $this;
    }

    /**
     * Returns a specific meta.
     *
     * @param string $key Key to return
     *
     * @throws \Elastica\Exception\InvalidException If requested key is not set
     *
     * @return mixed Key value
     */
    public function getMeta($key)
    {
        if (!$this->hasMeta($key)) {
            throw new InvalidException('Meta '.$key.' does not exist');
        }

        return $this->_metas[$key];
    }

    /**
     * Test if a meta is set.
     *
     * @param string $key Key to test
     *
     * @return bool True if the meta is set, false otherwise
     */
    public function hasMeta($key)
    {
        return isset($this->_metas[$key]);
    }

    /**
     * Returns the metas array.
     *
     * @return array Metas
     */
    public function getMetas()
    {
        return $this->_metas;
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

        $this->_aggs[] = $aggregation;

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
            $array = ['global' => new \stdClass()];
        }
        if (sizeof($this->_metas)) {
            $array['meta'] = $this->_convertArrayable($this->_metas);
        }
        if (sizeof($this->_aggs)) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
